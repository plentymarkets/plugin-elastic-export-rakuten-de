<?php

namespace ElasticExportRakutenDE\Services;

use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExportRakutenDE\Api\Client;
use ElasticExportRakutenDE\DataProvider\ElasticSearchDataProvider;
use ElasticExportRakutenDE\Helper\AttributeHelper;
use ElasticExportRakutenDE\Helper\PriceHelper;
use ElasticExport\Helper\ElasticExportSkuHelper;
use Exception;
use Illuminate\Support\Collection;
use Plenty\Modules\DataExchange\Contracts\ExportRepositoryContract;
use Plenty\Modules\DataExchange\Models\Export;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Market\Credentials\Contracts\CredentialsRepositoryContract;
use Plenty\Modules\Market\Credentials\Models\Credentials;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Modules\Helper\Models\KeyValue;

/**
 *
 * @class ItemUpdateService
 */
class ItemUpdateService
{
	use Loggable;

	const RAKUTEN_DE = 106.00;
	
	const KEY_ACCOUNT = 'credentials';
    const KEY_ACCOUNT_API_KEY = 'apiKey';
	const KEY_ELASTIC_EXPORT_SETTINGS = 'elasticExportSettings';
    const KEY_ELASTIC_EXPORT_FILTERS = 'elasticExportFilters';

	/**
	 * @var ElasticSearchDataProvider
	 */
	private $elasticSearchDataProvider;

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var CredentialsRepositoryContract
	 */
	private $credentialsRepositoryContract;

	/**
	 * @var PriceHelper
	 */
	private $priceHelper;

	/**
	 * @var ExportRepositoryContract $exportRepositoryContract
	 */
	private $exportRepositoryContract;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * @var ElasticExportStockHelper
     */
    private $elasticExportStockHelper;
    
    /**
	 * @var bool
	 */
	private $transferData = false;

	/**
	 * @var string
	 */
	private $endpoint = '';

	/**
	 * @var ElasticExportCoreHelper $elasticExportHelper
	 */
	private $elasticExportHelper;

	/**
	 * @var array
	 */
	private $filterList = array();

    /**
     * @var null|array
     */
	private $marketFilter = null;

	/**
	 * @var string
	 */
	private $apiKey = '';

    /** @var bool */
	public $exportStock = false;

    /** @var bool */
	public $exportPrice = false;
	
	/**
	 * @var array
	 */
	private $notFoundErrorCodes = [
		2210,
		2310
	];
	
    /**
     * @var ElasticExportSkuHelper
     */
    private $elasticExportSkuHelper;

    /**
     * @var VariationExportServiceContract
     */
    private $variationExportService;

    /**
     * ItemUpdateService constructor.
     *
     * @param ElasticSearchDataProvider $elasticSearchDataProvider
     * @param PriceHelper $priceHelper
     * @param Client $client
     * @param CredentialsRepositoryContract $credentialsRepositoryContract
     * @param ExportRepositoryContract $exportRepositoryContract
     * @param AttributeHelper $attributeHelper
     * @param ElasticExportStockHelper $elasticExportStockHelper
     * @param ElasticExportSkuHelper $elasticExportSkuHelper
     * @param VariationExportServiceContract $variationExportServiceContract
     */
	public function __construct(
		ElasticSearchDataProvider $elasticSearchDataProvider,
		PriceHelper $priceHelper,
		Client $client,
		CredentialsRepositoryContract $credentialsRepositoryContract,
		ExportRepositoryContract $exportRepositoryContract,
		AttributeHelper $attributeHelper,
        ElasticExportStockHelper $elasticExportStockHelper,
        ElasticExportSkuHelper $elasticExportSkuHelper,
        VariationExportServiceContract $variationExportServiceContract)
	{
		$this->elasticSearchDataProvider = $elasticSearchDataProvider;
		$this->client = $client;
		$this->credentialsRepositoryContract = $credentialsRepositoryContract;
		$this->priceHelper = $priceHelper;
		$this->exportRepositoryContract = $exportRepositoryContract;
		$this->elasticExportStockHelper = $elasticExportStockHelper;
		$this->attributeHelper = $attributeHelper;
        $this->elasticExportSkuHelper = $elasticExportSkuHelper;
        $this->variationExportService = $variationExportServiceContract;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

	/**
	 * Exports stock and price updates of multiple items and variations.
     * @throws Exception
	 */
	public function export()
	{
		$this->elasticExportHelper = pluginApp(ElasticExportCoreHelper::class);

		$accountDataList = $this->getRelevantAccountDataList();

        $elasticSearch = pluginApp(VariationElasticSearchScrollRepositoryContract::class);
        if (!$elasticSearch instanceof VariationElasticSearchScrollRepositoryContract) {
            return;
        }
        
		foreach ($accountDataList as $accountId => $accountData) {
            $this->elasticExportStockHelper->setAdditionalStockInformation($accountData[self::KEY_ELASTIC_EXPORT_SETTINGS]);
            $this->setFilters($accountData[self::KEY_ELASTIC_EXPORT_FILTERS]);
            $this->setApiKeyFromAccount($accountData[self::KEY_ACCOUNT]);
            $elasticSearch = $this->elasticSearchDataProvider->prepareElasticSearchSearch($elasticSearch, $accountData[self::KEY_ACCOUNT]);
            
            $currentItemId = $previousItemId = $this->marketFilter = null;
            $itemVariations = [];
            $shardIterator = 0;
            
            do {
                $resultList = $elasticSearch->execute();
                
                $shardIterator++;

                //log the amount of the ES result once
                if ($shardIterator == 1) {
                    $this->getLogger(__METHOD__)
                        ->addReference('total', (int)$resultList['total'])
                        ->info('ElasticExportRakutenDE::log.esResultAmount');
                }

                if ((is_array($resultList['error']) && count($resultList['error'])) ||
                    (is_string($resultList['error']) && strlen($resultList['error'])))
                {
                    $this->getLogger(__METHOD__)
                        ->addReference('failedShard', $shardIterator)
                        ->error('ElasticExportRakutenDE::log.occurredElasticSearchErrors', [
                            'error message' => $resultList['error'],
                        ]);
                }

                if (is_array($resultList['documents']) && count($resultList['documents'])) {
                    $this->elasticExportStockHelper->preloadStockAndPrice(
                        $resultList['documents'], $this->exportStock, $this->exportPrice
                    );
                    
                    foreach ($resultList['documents'] as $variation) {
                        if (!isset($currentItemId)) {
                            $previousItemId = $variation['data']['item']['id'];
                        }
                        $currentItemId = $variation['data']['item']['id'];

                        // Check if variation still belongs to same item
                        if ($currentItemId == $previousItemId) {
                            $itemVariations[] = $variation;
                        } else {
                            $this->exportItem($itemVariations, $accountData[self::KEY_ELASTIC_EXPORT_SETTINGS]);

                            $itemVariations = [];
                            $itemVariations[] = $variation;
                            $this->attributeHelper->resetAttributeCache();
                            $previousItemId = $variation['data']['item']['id'];
                        }
                    }
                }

            } while ($elasticSearch->hasNext());

            // Write the last batch of variations
            if (is_array($itemVariations) && count($itemVariations)) {
                $this->exportItem($itemVariations, $accountData[self::KEY_ELASTIC_EXPORT_SETTINGS]);
            }
            
            $this->elasticExportSkuHelper->finish();
            $this->client->writeLogs();
            // Reset internal price helper info that these info can be preloaded again by the next exports settings.
            $this->priceHelper->reset();
        }
	}

    /**
     * @return array
     */
	private function getRelevantAccountDataList():array 
    {
        $accountDataList = [];

        $accountDataList = $this->getAccounts($accountDataList);
        
        /* if no account found following steps are unnecessary*/
        if (count($accountDataList)) {
            $accountDataList = $this->getAccountExportData($accountDataList);
            $accountDataList = $this->getCleanedAccountDataList($accountDataList);
        }
        
        return $accountDataList;
    }

    /**
     * @param array $accountDataList
     * @return array
     */
    private function getAccounts(array $accountDataList):array
    {
        /* Get all relevant accounts */
        $rakutenCredentialList = $this->credentialsRepositoryContract->search(['market' => 'rakuten.de']);
        if ($rakutenCredentialList instanceof PaginatedResult) {
            foreach($rakutenCredentialList->getResult() as $account) {
                if ($account instanceof Credentials && !$this->isRelevantAccount($account, $accountDataList)) {
                    $accountDataList[(int)$account->id][self::KEY_ACCOUNT] = $account;
                    $accountDataList[(int)$account->id][self::KEY_ACCOUNT_API_KEY] = $account->data['key'];
                }
            }
        }
        return $accountDataList;
    }

    /**
     * Checks two things:
     *  1. if api key exists
     *  2. if api key is already in use (multiple plenty accounts for same Rakuten account)
     *
     * @param Credentials   $account
     * @param array         $processedAccounts
     * @return bool
     */
    private function isRelevantAccount(Credentials $account, array $processedAccounts):bool
    {
        if (!strlen(trim($account->data['key']))) {
            return false;
        }
        /* Get an array with all api keys */
        $apiKeys = array_column($processedAccounts, self::KEY_ACCOUNT_API_KEY);

        return (bool)array_search($account->data['key'], $apiKeys, true);
    }

    /**
     * @param array $accountDataList
     * @return array
     */
    private function getAccountExportData(array $accountDataList):array
    {
        /* Get settings and filters of first export per account */
        $elasticExports = $this->exportRepositoryContract->search(['formatKey' => 'RakutenDE-Plugin']);
        if ($elasticExports instanceof PaginatedResult) {
            foreach ($elasticExports->getResult() as $elasticExport) {
                if ($elasticExport instanceof Export) {
                    $settings = $elasticExport->formatSettings->all();
                    $settings = pluginApp(ArrayHelper::class)->buildMapFromObjectList($settings, 'key', 'value');
                    if ($settings instanceof KeyValue) {
                        $accountId = (int)$settings->get('marketAccountId');
                        if ($this->isExportRelevant($accountId, $accountDataList) &&
                            !$this->isAccountAlreadyMatchedToAnExport($accountId, $accountDataList))
                        {
                            $accountDataList[$accountId][self::KEY_ELASTIC_EXPORT_SETTINGS] = $settings;
                            $accountDataList[$accountId][self::KEY_ELASTIC_EXPORT_FILTERS] = $elasticExport->filters->toBase();
                        }
                    }
                }
            }
        }
        return $accountDataList;
    }

    /**
     * @param int   $accountId
     * @param array $accountDataList
     * @return bool
     */
    private function isExportRelevant(int $accountId, array $accountDataList):bool
    {
        return array_key_exists($accountId, $accountDataList);
    }
    
    /**
     * Checks if relevant account is already matched with an export
     * 
     * @param int   $accountId
     * @param array $accountDataList
     * @return bool
     */
    private function isAccountAlreadyMatchedToAnExport(int $accountId, array $accountDataList):bool
    {
        return isset($accountDataList[$accountId][self::KEY_ELASTIC_EXPORT_SETTINGS]);
    }

    /**
     * @param array $accountDataList
     * @return array
     */
    private function getCleanedAccountDataList(array $accountDataList):array 
    {
        foreach ($accountDataList as $accountId => $accountData) {
            
            if (!$accountData[self::KEY_ACCOUNT] instanceof Credentials ||
                !$accountData[self::KEY_ELASTIC_EXPORT_SETTINGS] instanceof KeyValue ||
                !$accountData[self::KEY_ELASTIC_EXPORT_FILTERS] instanceof Collection)
            {
                unset ($accountDataList[$accountId]);
            } else {
                unset ($accountData[self::KEY_ACCOUNT_API_KEY]);
            }
        }
        return $accountDataList;
    }

    /**
     * @param Collection $filters
     */
    private function setFilters(Collection $filters)
    {
        $this->filterList = [];
        foreach ($filters as $filter) {
            if (substr_count($filter['key'], '.') > 1) {
                $lastPos = strrpos($filter['key'], '.');
                $mainKey = substr($filter['key'], 0, $lastPos);
                $subKey  = substr($filter['key'], $lastPos + 1);

                $this->filterList[$mainKey][$subKey] = $filter['value'];
            } else {
                $this->filterList[$filter['key']] = $filter['value'];
            }
        }
    }
    
    private function setApiKeyFromAccount(Credentials $account)
    {
        $this->apiKey = $account->data['key'];
    }
	
	/**
     * Prepares the content for the request and selects the URL endpoint.
     *
	 * @param array $variation
	 * @param string $endPoint
	 * @param KeyValue $settings
	 * @return array
	 */
	private function prepareContent(array $variation, string $endPoint, KeyValue $settings):array
	{
        if ($variation['data']['skus'][0]['status'] !== VariationSku::MARKET_STATUS_ACTIVE) {
            return [];
        }
        $lastStockUpdateTimestamp = $this->getLastStockUpdateTimestamp($variation);

        $content = [];
        
		if ($this->exportStock) {
            $content = $this->getStockContent($content, $variation, $lastStockUpdateTimestamp, $endPoint); 
        }
		
		if ($this->exportPrice) {
            $content = $this->getPriceContent($content, $variation, $settings, $lastStockUpdateTimestamp);
		}

        if (count($content)) {
            switch ($endPoint) {
                case Client::EDIT_PRODUCT_VARIANT:
                case Client::EDIT_PRODUCT_MULTI_VARIANT:
                    $content[Client::VARIANT_ART_NO] = $variation['data']['skus'][0]['sku'];
                    break;
                default:
                    $content[Client::PRODUCT_ART_NO] = $variation['data']['skus'][0]['sku'];
            }
        }

		return $content;
	}

	/**
	 * @param array $variation
	 * @return bool
	 */
	private function isVariationDeactivatedByFilter(array $variation):bool 
	{
		$isActive = $hasMandatoryMarketLinks = true;
		
		if (isset($this->filterList['markets']) && is_string($this->filterList['markets'])) {
            $hasMandatoryMarketLinks = $this->hasVariationMandatoryMarketLinks($variation);
		}

		if (isset($this->filterList['isActive']) && is_string($this->filterList['isActive'])) {
            $isActive = $this->isVariationActive($variation, $this->filterList['isActive']);
		}

		return !$isActive || !$hasMandatoryMarketLinks;
	}

    /**
     * @param array $variation
     * @return bool
     */
	private function hasVariationMandatoryMarketLinks (array $variation):bool
    {
        if (!isset($this->marketFilter)) {
            $this->cacheMarketFilter();
        }
        
        $linkedMarketIds = $variation['data']['ids']['markets'];

        if (!is_array($this->marketFilter) || !count($this->marketFilter)) {
            return true;
        } elseif (is_array($linkedMarketIds) && count($linkedMarketIds)) {
            $linkedMarketIds = array_unique($linkedMarketIds);
            $match = array_intersect($this->marketFilter, $linkedMarketIds);

            if (count($match) == count($this->marketFilter)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function cacheMarketFilter()
    {
        if (isset($this->filterList['markets']) && is_string($this->filterList['markets'])) {
            $marketIds = explode(',', $this->filterList['markets']);
            if (is_array($marketIds)) {
                foreach($marketIds as $key => $value) {
                    if (strlen($value)) {
                        if (!strpos($value, '.')) {
                            $this->marketFilter[] = $value.'.00';
                        } else {
                            $this->marketFilter[] = $value;
                        }
                    }
                }
            }
        }
        if (!isset($this->marketFilter)) {
            $this->marketFilter = [];
        }
	}

    /**
     * @param array $variation
     * @return int
     */
	private function getLastStockUpdateTimestamp (array $variation):int
    {
        $lastStockUpdateTimestamp = strtotime($variation['data']['skus'][0]['stockUpdatedAt']);
        if (!is_int($lastStockUpdateTimestamp)) {
            $lastStockUpdateTimestamp = 0;
        }
        return $lastStockUpdateTimestamp;
    }

    /**
     * @param array $variation
     * @param string $filterSetting
     * @return bool
     */
    private function isVariationActive(array $variation, string $filterSetting):bool
    {
        if ($filterSetting == 'active' && $variation['data']['variation']['isActive'] != true) {
            return false;
        } elseif ($filterSetting == 'inactive' && $variation['data']['variation']['isActive'] != false) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * @param array $content
     * @param array $variation
     * @param int $lastStockUpdateTimestamp
     * @param string $endPoint
     * @return array
     */
    private function getStockContent(array $content, array $variation, int $lastStockUpdateTimestamp, string $endPoint):array
    {
        if (!$this->isVariationDeactivatedByFilter($variation)) {
            $data = $this->variationExportService->getData(VariationExportServiceContract::STOCK, $variation['id']);
            $stockList = $this->elasticExportStockHelper->getStockByPreloadedValue($variation, $data);

            //stock update is not necessary
            if (!$this->isStockUpdateNecessary($stockList, $lastStockUpdateTimestamp)) {
                if (is_array($stockList) && count($stockList)) {
                    $content['stock'] = $stockList['stock'] > 0 ? $stockList['stock'] : 0;

                    if ($stockList['stock'] > 0) {
                        $content['available'] = 1;
                    } else {
                        $content['available'] = 0;
                    }

                    if ($endPoint == Client::EDIT_PRODUCT) {
                        if ($stockList['inventoryManagementActive'] == 1) {
                            $content['stock_policy'] = 1;
                        } else {
                            $content['stock_policy'] = 0;
                        }
                    }
                }
            }
        } else {
            $content['available'] = 0;
            $content['stock'] = 0;

            if ($endPoint == Client::EDIT_PRODUCT) {
                $content['stock_policy'] = 1;
            }

            $this->elasticExportSkuHelper->updateStatus(
                (int)$variation['data']['skus'][0]['id'], VariationSku::MARKET_STATUS_INACTIVE
            );
        }
        
        return $content;
    }

    /**
     * @param array $stockList
     * @param int $lastStockUpdateTimestamp
     * @return bool
     */
    private function isStockUpdateNecessary(array $stockList, int $lastStockUpdateTimestamp):bool
    {
        if (isset($stockList['updatedAt']) && strlen($stockList['updatedAt'])) {
            if (strtotime($stockList['updatedAt']) > $lastStockUpdateTimestamp) {
                return true;
            }
        } else {
            if ($stockList['inventoryManagementActive'] != 1) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param array $content
     * @param array $variation
     * @param KeyValue $settings
     * @param int $lastStockUpdateTimestamp
     * @return array
     */
    private function getPriceContent(array $content, array $variation, KeyValue $settings, int $lastStockUpdateTimestamp):array
    {
        $preloadedPrices = $this->variationExportService->getData(VariationExportServiceContract::SALES_PRICE, $variation['id']);
        
        if (is_array($preloadedPrices)) {
            $priceList = $this->priceHelper->getPriceData($settings, $preloadedPrices);
        } else {
            $priceList = [];
        }
        //price update is not necessary
        if ($this->isPriceUpdateNecessary($priceList, $lastStockUpdateTimestamp)) {
            if (isset($priceList['price']) && $priceList['price'] > 0) {
                $content['price'] = number_format((float)$priceList['price'], 2, '.', '');
            }

            if (isset($priceList['reducedPrice']) && $priceList['reducedPrice'] > 0) {
                $content['price_reduced'] = number_format((float)$priceList['reducedPrice'], 2, '.', '');
                $content['price_reduced_type'] = $priceList['referenceReducedPrice'];
            }
        }
        
        return $content;
    }

    /**
     * @param array $priceList
     * @param int $lastStockUpdateTimestamp
     * @return bool
     */
    private function isPriceUpdateNecessary(array $priceList, int $lastStockUpdateTimestamp):bool
    {
        if (isset($priceList['variationPriceUpdatedTimestamp']) &&
            $priceList['variationPriceUpdatedTimestamp'] > $lastStockUpdateTimestamp)
        {
            return true;
        }

        if (isset($priceList['reducedPrice']) && strlen($priceList['reducedPrice']) &&
            isset($priceList['referenceReducedPrice']) && strlen($priceList['referenceReducedPrice']) )
        {
            if (isset($priceList['reducedPriceUpdatedTimestamp']) &&
                strtotime($priceList['reducedPriceUpdatedTimestamp']) > $lastStockUpdateTimestamp)
            {
                return true;
            }
        }
        
        return false;
    }

	/**
	 * @param array $variations
	 * @param KeyValue $settings
	 * @return void
     * @throws Exception
	 */
	private function exportItem(array $variations, KeyValue $settings)
	{
		if (is_array($variations) && count($variations) > 0)
		{
            $variations = $this->attributeHelper->getPreparedVariantItem($variations, $settings);
            
			foreach ($variations as $key => $variation) {
				/**
				 * gets the attribute value name of each attribute value which is linked with the variation in a specific order,
				 * which depends on the $attributeNameCombination
				 */
				$attributeValue = $this->attributeHelper->getRakutenAttributeValueString($variation, $settings);

				if (strlen($attributeValue)) {
                    $itemLevel = Client::EDIT_PRODUCT_MULTI_VARIANT;
                } else {
				    $itemLevel = Client::EDIT_PRODUCT;
                }

                $this->sendRequest($variation, $itemLevel, $settings);
			}
		}
	}

	/**
     * Sends the product or variant request.
     *
	 * @param array $variation
	 * @param string $endPoint
	 * @param KeyValue $settings
     * @throws Exception
	 */
	private function sendRequest($variation, $endPoint, $settings)
	{
	    if (!in_array($endPoint, [Client::EDIT_PRODUCT_MULTI_VARIANT, Client::EDIT_PRODUCT, Client::EDIT_PRODUCT_VARIANT])) {
            $this->getLogger(__METHOD__)
                ->addReference('variationId', $variation['id'])
                ->error('ElasticExportRakutenDE::log.missingEndpoint');
            return;
        }
	    
		$content = $this->prepareContent($variation, $endPoint, $settings);

		if (is_array($content) && count($content)) {
			$content['key'] = $this->apiKey;

			$response = $this->client->call($endPoint, Client::POST, $content);
			if ($response instanceof \SimpleXMLElement) {
				if ($response->{'success'} == "1") {
				    $this->elasticExportSkuHelper->updateStockUpdatedAt($variation['data']['skus'][0]['id']);
                //will only be set if the variation was not found at rakuten.
				} elseif (in_array($response->{'errors'}->error->code, $this->notFoundErrorCodes)) {
                    $this->elasticExportSkuHelper->updateStatus(
                        $variation['data']['skus'][0]['id'], VariationSku::MARKET_STATUS_INACTIVE
                    );
				}
			}
		}
	}
}