<?php

namespace ElasticExportRakutenDE\Services;

use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExportRakutenDE\Api\Client;
use ElasticExportRakutenDE\DataProvider\ElasticSearchDataProvider;
use ElasticExportRakutenDE\Helper\AttributeHelper;
use ElasticExportRakutenDE\Helper\ItemUpdatePriceHelper;
use ElasticExportRakutenDE\Helper\PriceHelper;
use ElasticExportRakutenDE\Helper\SkuHelper;
use Plenty\Modules\DataExchange\Contracts\ExportRepositoryContract;
use Plenty\Modules\DataExchange\Models\Export;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationExportServiceContract;
use Plenty\Modules\Item\Variation\Services\ExportPreloadValue\ExportPreloadValue;
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

	const BOOL_TRUE = 'true';
	const BOOL_FALSE = 'false';

	/**
	 * @var MarketAttributeHelperRepositoryContract $marketAttributeHelperRepositoryContract
	 */
	private $marketAttributeHelperRepositoryContract;

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
	 * @var ItemUpdatePriceHelper
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
	 * @var string
	 */
	public $stockUpdate = '';

	/**
	 * @var string
	 */
	public $priceUpdate = '';

	/**
	 * @var array
	 */
	private $filterList = array();

	/**
	 * @var string
	 */
	private $apiKey = '';

	/**
	 * @var array
	 */
	private $notFoundErrorCodes = [
		2210,
		2310
	];

	/**
	 * @var bool
	 */
	private $statusWasUpdated = false;

    /**
     * @var SkuHelper
     */
    private $skuHelper;

    /**
     * @var VariationExportServiceContract
     */
    private $variationExportService;

    /**
     * ItemUpdateService constructor.
     *
     * @param ElasticSearchDataProvider $elasticSearchDataProvider
     * @param ItemUpdatePriceHelper $priceHelper
     * @param Client $client
     * @param CredentialsRepositoryContract $credentialsRepositoryContract
     * @param ExportRepositoryContract $exportRepositoryContract
     * @param AttributeHelper $attributeHelper
     * @param ElasticExportStockHelper $elasticExportStockHelper
     * @param SkuHelper $skuHelper
     */
	public function __construct(
		MarketAttributeHelperRepositoryContract $marketAttributeHelperRepositoryContract,
		ElasticSearchDataProvider $elasticSearchDataProvider,
		ItemUpdatePriceHelper $priceHelper,
		Client $client,
		CredentialsRepositoryContract $credentialsRepositoryContract,
		ExportRepositoryContract $exportRepositoryContract,
		AttributeHelper $attributeHelper,
        ElasticExportStockHelper $elasticExportStockHelper,
        SkuHelper $skuHelper,
        VariationExportServiceContract $variationExportServiceContract)
	{
		$this->marketAttributeHelperRepositoryContract = $marketAttributeHelperRepositoryContract;
		$this->elasticSearchDataProvider = $elasticSearchDataProvider;
		$this->client = $client;
		$this->credentialsRepositoryContract = $credentialsRepositoryContract;
		$this->priceHelper = $priceHelper;
		$this->exportRepositoryContract = $exportRepositoryContract;
		$this->elasticExportStockHelper = $elasticExportStockHelper;
		$this->attributeHelper = $attributeHelper;
        $this->skuHelper = $skuHelper;
        $this->variationExportService = $variationExportServiceContract;
    }

	/**
	 * Generates the content for updating stock and price of multiple items and variations.
     * @throws \Exception
	 */
	public function generateContent()
	{
		$this->elasticExportHelper = pluginApp(ElasticExportCoreHelper::class);

		$currentItemId = null;
		$previousItemId = null;
		$variations = array();
		$shardIterator = 0;
		$elasticSearch = pluginApp(VariationElasticSearchScrollRepositoryContract::class);
		$exportList = $this->exportRepositoryContract->search(['formatKey' => 'RakutenDE-Plugin']);

		if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
		{
			$rakutenCredentialList = $this->credentialsRepositoryContract->search(['market' => 'rakuten.de']);
			if(!$rakutenCredentialList instanceof PaginatedResult)
			{
			    return;
			}

            /** @var Credentials $rakutenCredential */
            foreach($rakutenCredentialList->getResult() as $rakutenCredential)
            {
                if(!strlen(trim($rakutenCredential->data['key']))) {
                    continue;
                }

                $successfulIteration = false;

                /** @var Export $export */
                foreach($exportList->getResult() as $export)
                {
                    if($export instanceof Export && $successfulIteration === false)
                    {
                        $settings = $export->formatSettings->all();
                        $settings = pluginApp(ArrayHelper::class)->buildMapFromObjectList($settings, 'key', 'value');
                        $this->stockHelper->setAdditionalStockInformation($settings);

                        if($rakutenCredential instanceof Credentials)
                        {
                            if((int)$rakutenCredential->id != (int)$settings->get('marketAccountId'))
                            {
                                continue;
                            }

                            $successfulIteration = true;
                            $filters = $export->filters->toBase();

                            $this->filterList = [];
                            foreach($filters as $filter)
                            {
                                if(substr_count($filter['key'],'.') > 1)
                                {
                                    $lastPos = strrpos($filter['key'], '.');
                                    $mainKey = substr($filter['key'], 0, $lastPos);
                                    $subKey  = substr($filter['key'], $lastPos + 1);

                                    $this->filterList[$mainKey][$subKey] = $filter['value'];
                                }
                                else
                                {
                                    $this->filterList[$filter['key']] = $filter['value'];
                                }
                            }

                            $this->apiKey = $rakutenCredential->data['key'];

                            if($this->priceUpdate == self::BOOL_TRUE || $this->stockUpdate == self::BOOL_TRUE)
                            {
                                $elasticSearch = $this->elasticSearchDataProvider->prepareElasticSearchSearch($elasticSearch, $rakutenCredential);

                                do
                                {
                                    $resultList = $elasticSearch->execute();
                                    $shardIterator++;

                                    //log the amount of the elasticsearch result once
                                    if($shardIterator == 1)
                                    {
                                        $this->getLogger(__METHOD__)
                                            ->addReference('total', (int)$resultList['total'])
                                            ->info('ElasticExportRakutenDE::log.esResultAmount');
                                    }

                                    if(count($resultList['error']) > 0)
                                    {
                                        $this->getLogger(__METHOD__)
                                            ->addReference('failedShard', $shardIterator)
                                            ->error('ElasticExportRakutenDE::log.occurredElasticSearchErrors', [
                                                'error message' => $resultList['error'],
                                            ]);
                                    }

                                    if(is_array($resultList['documents']) && count($resultList['documents']) > 0)
                                    {
                                        // preloads data depending on settings by VariationExportServiceContract
                                        $this->preload($resultList['documents']);

                                        foreach($resultList['documents'] as $variation)
                                        {
                                            if ($currentItemId === null)
                                            {
                                                $previousItemId = $variation['data']['item']['id'];
                                            }

                                            $currentItemId = $variation['data']['item']['id'];

                                            // Check if it's the same item
                                            if ($currentItemId == $previousItemId)
                                            {
                                                $variations[] = $variation;
                                            }
                                            else
                                            {
                                                $this->parentChildSorting($variations, $settings);

                                                $variations = array();
                                                $variations[] = $variation;
                                                $previousItemId = $variation['data']['item']['id'];
                                            }
                                        }
                                    }

                                    if(strlen($resultList['error']))
                                    {
                                        $this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.esError', [
                                            'error message' => $resultList['error'],
                                        ]);
                                    }

                                } while ($elasticSearch->hasNext());

                                // Write the last batch of variations
                                if (is_array($variations) && count($variations) > 0)
                                {
                                    $content = array();
                                    $content['stock'] = 0;
                                    $this->parentChildSorting($variations, $settings);

                                    unset($variations);
                                }

                                $this->skuHelper->finish();
                                $this->client->writeLogs();
                            }
                        }

                        // Reset internal price helper info that these info can be preloaded again by the next exports settings.
                        $this->priceHelper->reset();
                    }
                }
            }
		}
	}

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
	
	/**
     * Prepares the content for the request and selects the URL endpoint.
     *
	 * @param array $variation
	 * @param string $itemLevel
	 * @param KeyValue $settings
	 * @return null|array
	 */
	private function prepareContent($variation, $itemLevel, $settings)
	{
		$content = null;
		$stillActive = $this->stillActive($variation);
		$sku = $variation['data']['skus'][0]['sku'];

		$lastStockUpdateTimestamp = strtotime($variation['data']['skus'][0]['stockUpdatedAt']);

		if($lastStockUpdateTimestamp === false) {
            $lastStockUpdateTimestamp = 0;
        }

		if ($variation['data']['skus'][0]['status'] == VariationSku::MARKET_STATUS_INACTIVE)
		{
			return $content;
		}

		if(!is_null($itemLevel) && strlen($itemLevel) && $itemLevel == Client::EDIT_PRODUCT_VARIANT)
		{
			$content[Client::VARIANT_ART_NO] = $sku;
			$this->endpoint = Client::EDIT_PRODUCT_VARIANT;
		}
		elseif(!is_null($itemLevel) && strlen($itemLevel) && $itemLevel == Client::EDIT_PRODUCT)
		{
			$content[Client::PRODUCT_ART_NO] = $sku;
			$this->endpoint = Client::EDIT_PRODUCT;
		}
		elseif(!is_null($itemLevel) && strlen($itemLevel) && $itemLevel == Client::EDIT_PRODUCT_MULTI_VARIANT)
		{
			$content[Client::VARIANT_ART_NO] = $sku;
			$this->endpoint = Client::EDIT_PRODUCT_MULTI_VARIANT;
		}
		else
		{
			$this->getLogger(__METHOD__)
                ->addReference('variationId', $variation['id'])
                ->error('ElasticExportRakutenDE::log.missingEndpoint');

			return null;
		}

		if($this->stockUpdate == self::BOOL_TRUE && $stillActive === true)
		{
            $data = $this->variationExportService->getData(VariationExportServiceContract::STOCK, $variation['id']);
			$stockList = $this->stockHelper->getStockByPreloadedValue($variation, $data);

			if(count($stockList))
			{
				$content['stock'] = $stockList['stock'];
				$content['available'] = 0;

				if($stockList['stock'] > 0 || $stockList['inventoryManagementActive'] == 2)
				{
					$content['available'] = 1;
				}

				if($this->endpoint == Client::EDIT_PRODUCT)
				{
					if($stockList['inventoryManagementActive'] == 1)
					{
						$content['stock_policy'] = 1;
					}
					else
					{
						$content['stock_policy'] = 0;
					}
				}

				if((strlen($stockList['updatedAt']) && $stockList['updatedAt'] > $lastStockUpdateTimestamp) ||
                        is_null($variation['data']['skus'][0]['stockUpdatedAt'])) {
                    $this->transferData = true;
				}
			}
		}
		elseif($this->stockUpdate == self::BOOL_TRUE && $stillActive === false && $variation['data']['skus'][0]['status'] == VariationSku::MARKET_STATUS_ACTIVE)
		{
			$content['available'] = 0;
			$content['stock'] = 0;

            if($this->endpoint == Client::EDIT_PRODUCT)
            {
                $content['stock_policy'] = 1;
            }

			$this->transferData = true;

			$this->skuHelper->updateStatus((int)$variation['data']['skus'][0]['id'], VariationSku::MARKET_STATUS_INACTIVE);
			$this->statusWasUpdated = true;
		}

		if($this->priceUpdate == self::BOOL_TRUE && $stillActive === true)
		{
		    $preloadedPrices = $this->variationExportService->getData(VariationExportServiceContract::SALES_PRICE, $variation['id']);
		    if(is_array($preloadedPrices)) {
                $priceList = $this->priceHelper->getPriceData($settings, $preloadedPrices);
            } else {
                $priceList = [];
            }

			if (isset($priceList['price']) && $priceList['price'] > 0) {
				$price = number_format((float)$priceList['price'], 2, '.', '');
				$priceUpdateTime = strtotime($priceList['variationPriceUpdatedTimestamp']);

				if(!is_null($priceUpdateTime) &&
					($priceUpdateTime > $lastStockUpdateTimestamp ||
						is_null($variation['data']['skus'][0]['stockUpdatedAt']))) {
					$this->transferData = true;
					$content['price'] = $price;
				}
			}
			if (isset($priceList['reducedPrice']) && $priceList['reducedPrice'] > 0) {
				$reducedPrice = number_format((float)$priceList['reducedPrice'], 2, '.', '');
				$reducedPriceUpdateTime = strtotime($priceList['reducedPriceUpdatedTimestamp']);
				$referenceReducedPrice = $priceList['referenceReducedPrice'];

				if (!is_null($reducedPriceUpdateTime) &&
					($reducedPriceUpdateTime > $lastStockUpdateTimestamp ||
						is_null($variation['data']['skus'][0]['stockUpdatedAt'])) &&
					$reducedPrice < $priceList['price']) {

					$this->transferData = true;
					$content['price_reduced'] = $reducedPrice;
					$content['price_reduced_type'] = $referenceReducedPrice;
				}
			}
		}

		return $content;
	}

	/**
	 * @param array $variation
	 * @return bool
	 */
	private function stillActive($variation)
	{
		$stillActive = true;

		if(array_key_exists('markets', $this->filterList))
		{
			$markets = explode(',', $this->filterList['markets']);
			$marketIds = $variation['data']['ids']['markets'];

			if(count($marketIds) > 0 && strlen($markets[0]) > 0)
			{
				foreach($markets as $key => $value)
				{
					if(!strpos($markets[$key], '.'))
					{
						$markets[$key] = $value.'.00';
					}
				}

				$marketIds = array_unique($marketIds);
				$match = array_intersect($markets, $marketIds);

				if(count($match) != count($markets))
				{
					return false;
				}
			}

			//triggers only if a valid filter is set and if the variation has no referrer
			elseif(strlen($markets[0]) > 0)
			{
				return false;
			}
		}

		if(array_key_exists('isActive', $this->filterList))
		{
			if(
				($this->filterList['isActive'] == 'active' && $variation['data']['variation']['isActive'] != true)
				|| ($this->filterList['isActive'] == 'inactive' && $variation['data']['variation']['isActive'] != false))
			{
				return false;
			}
		}

		return $stillActive;
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
	 * @param string $itemLevel
	 * @param KeyValue|null $settings
     * @throws \Exception
	 */
	private function sendRequest($variation, $itemLevel, $settings)
	{
		$preparedContent = $this->prepareContent($variation, $itemLevel, $settings);

		if(!is_null($preparedContent) && is_array($preparedContent) && count($preparedContent))
		{
			$content = $preparedContent;
			$content['key'] = $this->apiKey;

			$response = $this->client->call($this->endpoint, Client::POST, $content);
			if($response instanceof \SimpleXMLElement)
			{
				if($response->success == "1")
				{
				    if($this->statusWasUpdated === false)
				    {
						$this->skuHelper->updateStatus($variation['data']['skus'][0]['id'], VariationSku::MARKET_STATUS_ACTIVE);
                        $this->skuHelper->updateStockUpdatedAt($variation['data']['skus'][0]['id']);
					}
				}
				//will only be set if the variation was not found at rakuten.
				elseif(in_array($response->errors->error->code, $this->notFoundErrorCodes) && $this->statusWasUpdated === false)
				{
                    $this->skuHelper->updateStatus($variation['data']['skus'][0]['id'], VariationSku::MARKET_STATUS_INACTIVE);
				}
			}
		}

		$this->statusWasUpdated = false;
	}

    private function preload(array $documents)
    {
        $types = [];
        if($this->stockUpdate == self::BOOL_TRUE) {
            $types[] = VariationExportServiceContract::STOCK;
        }

        if($this->priceUpdate == self::BOOL_TRUE) {
            $types[] = VariationExportServiceContract::SALES_PRICE;
        }

        $this->variationExportService->addPreloadTypes($types);

        // collect item IDs and variation IDs for preload
        $values = [];
        foreach ($documents AS $variation) {
            $values[] = pluginApp(ExportPreloadValue::class, [
                0 => (int)$variation['data']['item']['id'],
                1 => (int)$variation['id']
            ]);
        }
        $this->variationExportService->preload($values);
    }
}