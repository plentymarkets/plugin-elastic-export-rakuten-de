<?php

namespace ElasticExportRakutenDE\Services;

use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExportRakutenDE\Api\Client;
use ElasticExportRakutenDE\DataProvider\ElasticSearchDataProvider;
use ElasticExportRakutenDE\Helper\PriceHelper;
use ElasticExportRakutenDE\Helper\StockHelper;
use Plenty\Modules\DataExchange\Contracts\ExportRepositoryContract;
use Plenty\Modules\DataExchange\Models\Export;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Market\Credentials\Contracts\CredentialsRepositoryContract;
use Plenty\Modules\Market\Credentials\Models\Credentials;
use Plenty\Modules\Market\Helper\Contracts\MarketAttributeHelperRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
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
	 * @var PriceHelper
	 */
	private $priceHelper;

	/**
	 * @var ConfigRepository
	 */
	private $configRepository;
	/**
	 * @var ExportRepositoryContract $exportRepositoryContract
	 */
	private $exportRepositoryContract;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepository;

	/**
	 * @var StockHelper
	 */
	private $stockHelper;

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
	private $stockUpdate = '';

	/**
	 * @var string
	 */
	private $priceUpdate = '';

	/**
	 * @var array
	 */
	private $attributeName = array();

	/**
	 * @var array
	 */
	private $attributeNameCombination = array();

	/**
	 * @var array
	 */
	private $filterList = array();

	/**
	 * @var string
	 */
	private $apiKey = '';

	/**
	 * ItemUpdateService constructor.
	 * @param MarketAttributeHelperRepositoryContract $marketAttributeHelperRepositoryContract
	 * @param ElasticSearchDataProvider $elasticSearchDataProvider
	 * @param PriceHelper $priceHelper
	 * @param Client $client
	 * @param CredentialsRepositoryContract $credentialsRepositoryContract
	 * @param ConfigRepository $configRepository
	 * @param ExportRepositoryContract $exportRepositoryContract
	 * @param VariationSkuRepositoryContract $variationSkuRepository
	 * @param StockHelper $stockHelper
	 */
	public function __construct(
		MarketAttributeHelperRepositoryContract $marketAttributeHelperRepositoryContract,
		ElasticSearchDataProvider $elasticSearchDataProvider,
		PriceHelper $priceHelper,
		Client $client,
		CredentialsRepositoryContract $credentialsRepositoryContract,
		ConfigRepository $configRepository,
		ExportRepositoryContract $exportRepositoryContract,
		VariationSkuRepositoryContract $variationSkuRepository,
		StockHelper $stockHelper)
	{
		$this->marketAttributeHelperRepositoryContract = $marketAttributeHelperRepositoryContract;
		$this->elasticSearchDataProvider = $elasticSearchDataProvider;
		$this->client = $client;
		$this->credentialsRepositoryContract = $credentialsRepositoryContract;
		$this->priceHelper = $priceHelper;
		$this->configRepository = $configRepository;
		$this->exportRepositoryContract = $exportRepositoryContract;
		$this->variationSkuRepository = $variationSkuRepository;
		$this->stockHelper = $stockHelper;
	}

	/**
	 * Generates the content for updating stock and price of multiple items
	 * and variations.
	 *
	 */
	public function generateContent()
	{
		$this->elasticExportHelper = pluginApp(ElasticExportCoreHelper::class);

		$currentItemId = null;
		$previousItemId = null;
		$variations = array();
		$newShard = false;
		$shardIterator = 0;
		$elasticSearch = pluginApp(VariationElasticSearchScrollRepositoryContract::class);
		$exportList = $this->exportRepositoryContract->search(['formatKey' => 'RakutenDE-Plugin']);

		if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
		{
			$rakutenCredentialList = $this->credentialsRepositoryContract->search(['market' => 'rakuten.de']);
			if($rakutenCredentialList instanceof PaginatedResult)
			{
				foreach($rakutenCredentialList->getResult() as $rakutenCredential)
				{
					$successfulIteration = false;

					foreach($exportList->getResult() as $export)
					{
						if($export instanceof Export && $successfulIteration === false)
						{
							$settings = $export->formatSettings->all();
							$settings = pluginApp(ArrayHelper::class)->buildMapFromObjectList($settings, 'key', 'value');
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

								$this->priceUpdate = $this->configRepository->get('ElasticExportRakutenDE.update_settings.price_update');
								$this->stockUpdate = $this->configRepository->get('ElasticExportRakutenDE.update_settings.stock_update');

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
											$this->getLogger(__METHOD__)->addReference('total', (int)$resultList['total'])->info('ElasticExportRakutenDE::log.esResultAmount');
										}

										if(count($resultList['error']) > 0)
										{
											$this->getLogger(__METHOD__)->addReference('failedShard', $shardIterator)->error('ElasticExportRakutenDE::log.occurredElasticSearchErrors', [
												'error message' => $resultList['error'],
											]);
										}

										if(is_array($resultList['documents']) && count($resultList['documents']) > 0)
										{
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
													$this->parentChildSorting($variations, $newShard, $settings);

													$newShard = false;
													$variations = array();
													$variations[] = $variation;
													$previousItemId = $variation['data']['item']['id'];
												}
											}
											// Write the last batch of variations
											if (is_array($variations) && count($variations) > 0)
											{
												$content = array();
												$content['stock'] = 0;
												$this->parentChildSorting($variations, $newShard, $settings);

												$newShard = true;
												unset($variations);
											}
										}

										if(strlen($resultList['error']))
										{
											$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.esError', [
												'error message' => $resultList['error'],
											]);
										}

									} while ($elasticSearch->hasNext());
								}
							}
						}
					}
				}
			}
		}
	}

	/**
     * Prepares the content for the request and selects the URL endpoint.
     * 
	 * @param array $variation
	 * @param string $itemLevel
	 * @param KeyValue $settings
     * @param bool $isVariantProduct
	 * @return null|array
	 */
	private function prepareContent($variation, $itemLevel, $settings, $isVariantProduct)
	{
		$content = null;
		$stillActive = $this->stillActive($variation);
		$sku = $variation['data']['skus'][0]['sku'];

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
		else
		{
			$this->getLogger(__METHOD__)->addReference('variationId', $variation->id)->error('ElasticExportRakutenDE::log.missingEndpoint');
			return null;
		}

		if($this->stockUpdate == self::BOOL_TRUE && $stillActive === true)
		{
			$stockList = $this->stockHelper->getStockList($variation);
			if(count($stockList))
			{
				$content['stock'] = $stockList['stock'];

				if(!$isVariantProduct && ($stockList['stock'] > 0 || $stockList['inventoryManagementActive'] == 2))
				{
					$content['available'] = 1;
				}

				if($this->endpoint == Client::EDIT_PRODUCT)
				{
					if($stockList['inventoryManagementActive'] == 1)
					{
						$content['stock_policy'] = 1;
					}
				}

				if((!is_null($stockList['updatedAt']) && $stockList['updatedAt'] > strtotime($variation['data']['skus'][0]['exportedAt'])))
				{
					$this->transferData = true;
				}
			}
		}
		elseif($this->stockUpdate == self::BOOL_TRUE && $stillActive === false)
		{
			$content['available'] = 1;
			$content['stock'] = 0;

            if($this->endpoint == Client::EDIT_PRODUCT)
            {
                $content['stock_policy'] = 1;
            }
			
			$this->transferData = true;
		}

		if($this->priceUpdate == self::BOOL_TRUE && $stillActive === true)
		{
			$priceResponse = $this->priceHelper->getPrice($variation, $settings);
			if($priceResponse instanceof SalesPriceSearchResponse)
			{
				if($priceResponse->price > 0)
				{
					$price = number_format((float)$priceResponse->price, 2, '.', '');
				}
				else
				{
					$price = '';
				}

				if(!is_null($priceResponse->updatedAt) && $priceResponse->updatedAt > strtotime($variation['data']['skus'][0]['exportedAt']))
				{
					$this->transferData = true;
				}

				$content['price'] = $price;
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
	 * @param bool $crossShardConnection
	 * @param KeyValue|null $settings
	 * @return void
	 */
	private function parentChildSorting($variations, $crossShardConnection = false, $settings = null)
	{
		$potentialParent = null;
		$parentWithoutChildren = array();

		if (is_array($variations) && count($variations) > 0)
		{
			$primaryVariationKey = null;

			foreach($variations as $key => $variation)
			{
				/**
				 * Select and save the attribute name order for the first variation of each item with attributes,
				 * if the variation has attributes
				 */
				if (is_array($variation['data']['attributes']) &&
					count($variation['data']['attributes']) > 0 &&
					!array_key_exists($variation['data']['item']['id'], $this->attributeName) &&
					!array_key_exists($variation['data']['item']['id'], $this->attributeNameCombination))
				{
					$this->attributeName[$variation['data']['item']['id']] = $this->elasticExportHelper->getAttributeName($variation, $settings);
					foreach ($variation['data']['attributes'] as $attribute)
					{
						if(array_key_exists('attributeId', $attribute) && !is_null($attribute['attributeId']))
						{
							$this->attributeNameCombination[$variation['data']['item']['id']][] = $attribute['attributeId'];
						}
					}
					if(strlen($this->attributeName[$variation['data']['item']['id']]) == 0)
					{
						unset($this->attributeName[$variation['data']['item']['id']]);
					}
				}

				// note key of primary variation
				if(array_key_exists('isMain', $variation['data']['variation']) && $variation['data']['variation']['isMain'] === true)
				{
					$primaryVariationKey = $key;
				}
			}

			// change sort of array and add primary variation as first entry
			if(!is_null($primaryVariationKey))
			{
				$primaryVariation = $variations[$primaryVariationKey];
				unset($variations[$primaryVariationKey]);
				array_unshift($variations, $primaryVariation);
			}

			$i = 1;

			foreach($variations as $key => $variation)
			{
				/**
				 * gets the attribute value name of each attribute value which is linked with the variation in a specific order,
				 * which depends on the $attributeNameCombination
				 */
				$attributeValue = $this->elasticExportHelper->getAttributeValueSetShortFrontendName($variation, $settings, '|', $this->attributeNameCombination[$variation['data']['item']['id']]);

				if(!is_null($potentialParent) && strlen($attributeValue))
				{
					$itemLevel = Client::EDIT_PRODUCT_VARIANT;
					$this->sendRequest($variation, $itemLevel, $settings, true);

					unset($potentialParent);
				}

				/**
				 * If it is a new elastic search shard and the first entries are variations from the
				 * last entries of the shard before, the connected variations will be added as children.
				 */
				elseif($crossShardConnection === true)
				{
					$itemLevel = Client::EDIT_PRODUCT_VARIANT;
					$this->sendRequest($variation, $itemLevel, $settings, true);
				}

				//isMain can be true or false, this does not matter in this case
				elseif(strlen($attributeValue) == 0 && count($variations) == 1)
				{
					$itemLevel = Client::EDIT_PRODUCT;
					$this->sendRequest($variation, $itemLevel, $settings, false);
				}

				//isMain can be true or false, this does not matter in this case
				elseif(count($variations) == 1 && strlen($attributeValue) > 0)
				{
					$itemLevel = Client::EDIT_PRODUCT_VARIANT;
					$this->sendRequest($variation, $itemLevel, $settings, false);
				}

				/**
				 * only if this is the first iteration
				 * && count($variations) > 1
				 * isMain can be true or false, this does not matter in this case
				 */
				elseif($i == 1 && strlen($attributeValue) > 0)
				{
					$itemLevel = Client::EDIT_PRODUCT_VARIANT;
					$this->sendRequest($variation, $itemLevel, $settings, true);
				}

				//&& count($variations) > 1
				elseif($variation['data']['variation']['isMain'] === true && strlen($attributeValue) == 0)
				{
					$potentialParent = $variation;
				}

				//no attributeValue, not Main and count($variations) > 1
				elseif(strlen($attributeValue) == 0 && $i == 1)
				{
					continue;
				}

				//count($variations) > 1 && isMain = false
				elseif(strlen($attributeValue) == 0)
				{
					$parentWithoutChildren[] = $variation;
				}

				else
				{
					$itemLevel = Client::EDIT_PRODUCT_VARIANT;
					$this->sendRequest($variation, $itemLevel, $settings, false);
				}

				$i++;
			}

			if(count($parentWithoutChildren) > 0)
			{
				foreach($parentWithoutChildren as $variation)
				{
					$itemLevel = Client::EDIT_PRODUCT;
					$this->sendRequest($variation, $itemLevel, $settings, false);
				}
			}
		}
	}

	/**
     * Sends the product or variant request.
     * 
	 * @param array $variation
	 * @param string $itemLevel
	 * @param KeyValue|null $settings
     * @param bool $isVariantProduct
	 */
	private function sendRequest($variation, $itemLevel, $settings, $isVariantProduct = false)
	{
		$preparedContent = $this->prepareContent($variation, $itemLevel, $settings, $isVariantProduct);

		if(!is_null($preparedContent) && is_array($preparedContent) && count($preparedContent))
		{
			$content = $preparedContent;
			$content['key'] = $this->apiKey;

			$response = $this->client->call($this->endpoint, Client::POST, $content);
			if($response instanceof \SimpleXMLElement)
			{
				if($response->success == "1")
				{
				    $this->getLogger(__METHOD__)->info('ElasticExportRakutenDE::log.stockUpdatedSuccessfully', [
                        'endpoint'          => $this->endpoint,
                        'request content'	=> $content
                    ]);
					$this->variationSkuRepository->update(['exportedAt' => date("Y-m-d H:i:s")], $variation['data']['skus'][0]['id']);
				}
			}
		}
	}
}