<?php

namespace ElasticExportRakutenDE\Services;

use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExportRakutenDE\Api\Client;
use ElasticExportRakutenDE\DataProvider\ElasticSearchDataProvider;
use ElasticExportRakutenDE\Helper\PriceHelper;
use Plenty\Modules\DataExchange\Contracts\ExportRepositoryContract;
use Plenty\Modules\DataExchange\Models\Export;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Market\Credentials\Contracts\CredentialsRepositoryContract;
use Plenty\Modules\Market\Credentials\Models\Credentials;
use Plenty\Modules\Market\Helper\Contracts\MarketAttributeHelperRepositoryContract;
use Plenty\Modules\StockManagement\Stock\Models\Stock;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;

/**
 *
 * @class ItemUpdateService
 */
class ItemUpdateService
{
	use Loggable;

	const RAKUTEN_DE = 106.00;

	const TWO_DAYS = 172800;

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
	 * ItemUpdateService constructor.
	 * @param MarketAttributeHelperRepositoryContract $marketAttributeHelperRepositoryContract
	 * @param ElasticSearchDataProvider $elasticSearchDataProvider
	 * @param PriceHelper $priceHelper
	 * @param Client $client
	 * @param CredentialsRepositoryContract $credentialsRepositoryContract
	 * @param ConfigRepository $configRepository
	 * @param ExportRepositoryContract $exportRepositoryContract
	 */
	public function __construct(
		MarketAttributeHelperRepositoryContract $marketAttributeHelperRepositoryContract,
		ElasticSearchDataProvider $elasticSearchDataProvider,
		PriceHelper $priceHelper,
		Client $client,
		CredentialsRepositoryContract $credentialsRepositoryContract,
		ConfigRepository $configRepository,
		ExportRepositoryContract $exportRepositoryContract)
	{
		$this->marketAttributeHelperRepositoryContract = $marketAttributeHelperRepositoryContract;
		$this->elasticSearchDataProvider = $elasticSearchDataProvider;
		$this->client = $client;
		$this->credentialsRepositoryContract = $credentialsRepositoryContract;
		$this->priceHelper = $priceHelper;
		$this->configRepository = $configRepository;
		$this->exportRepositoryContract = $exportRepositoryContract;
	}

	/**
	 * Generates the content for updating stock and price of multiple items
	 * and variations.
	 *
	 */
	public function generateContent()
	{
		$transferData = false;
		$elasticSearch = pluginApp(VariationElasticSearchScrollRepositoryContract::class);
		$elasticExportStockHelper = pluginApp(ElasticExportStockHelper::class);
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

								$filtersList = [];
								foreach($filters as $filter)
								{
									if(substr_count($filter['key'],'.') > 1)
									{
										$lastPos = strrpos($filter['key'], '.');
										$mainKey = substr($filter['key'], 0, $lastPos);
										$subKey  = substr($filter['key'], $lastPos + 1);

										$filtersList[$mainKey][$subKey] = $filter['value'];
									}

									else
									{
										$filtersList[$filter['key']] = $filter['value'];
									}
								}

								$apiKey = $rakutenCredential->data['key'];

								$priceUpdate = $this->configRepository->get('ElasticExportRakutenDE.update_settings.price_update');
								$stockUpdate = $this->configRepository->get('ElasticExportRakutenDE.update_settings.stock_update');

								if($priceUpdate == "true" || $stockUpdate == "true")
								{
									$elasticSearch = $this->elasticSearchDataProvider->prepareElasticSearchSearch($elasticSearch, $rakutenCredential);

									do
									{
										$resultList = $elasticSearch->execute();

										if(is_array($resultList['documents']) && count($resultList['documents']) > 0)
										{
											foreach($resultList['documents'] as $variation)
											{
												$content['stock'] = 0;

												$endPoint = $this->getEndpoint($variation);

												$content['key'] = $apiKey;

												$sku = $variation['data']['skus'][0]['sku'];

												//Need different content keys for the calls, depending on the update type
												if($endPoint == Client::EDIT_PRODUCT)
												{
													$content[Client::EDIT_PRODUCT] = $sku;
												}
												else
												{
													$content[Client::EDIT_PRODUCT_VARIANT] = $sku;
												}

												$stillActive = $this->stillActive($variation, $filtersList);

												if($priceUpdate == "true" && $stillActive === true)
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

														//checks if the price was updated within the last 2 days
														if($priceResponse->updatedAt > (time() - self::TWO_DAYS))
														{
															$transferData = true;
														}

														$content['price'] = $price;
													}

												}

												if($stockUpdate == "true" && $stillActive === true)
												{
													$stock = $elasticExportStockHelper->getStockObject($variation);

													if($stock instanceof Stock)
													{
														$content['stock'] = $stock->stockNet;

														//checks if the stock was updated within the last 2 days
														if($stock->updatedAt > (time() - self::TWO_DAYS))
														{
															$transferData = true;
														}
													}
												}

												if($transferData === false)
												{
													continue;
												}

												$this->client->call($endPoint, Client::POST, $content);
											}
										}

										if(strlen($resultList['error']))
										{
											$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.esError', [
												'error message' => $resultList['error']
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
	 * @param $variation
	 * @return string
	 */
	private function getEndpoint($variation):string
	{
		/**
		 * gets the attribute value name of each attribute value which is linked with the variation in a specific order,
		 * which depends on the $attributeNameCombination
		 */
		$attributeValue = $this->getAttributeValueSetShortFrontendName($variation);

		/**
		 * This is used to get the right endpoint depending on the variation is a parent or not
		 */
		if($variation['data']['variation']['isMain'] === false)
		{
			return Client::EDIT_PRODUCT_VARIANT;
		}
		elseif($variation['data']['variation']['isMain'] === true && count($attributeValue) > 0)
		{
			return Client::EDIT_PRODUCT_VARIANT;
		}
		elseif($variation['data']['variation']['isMain'] === true && count($attributeValue) == 0)
		{
			return Client::EDIT_PRODUCT;
		}
		else
		{
			return Client::EDIT_PRODUCT;
		}
	}

	/**
	 * @param $variation
	 * @return array
	 */
	private function getAttributeValueSetShortFrontendName($variation):array
	{
		$values = [];
		$unsortedValues = [];

		if(isset($variation['data']['attributes'][0]['attributeValueSetId'])
			&& !is_null($variation['data']['attributes'][0]['attributeValueSetId']))
		{
			$i = 0;

			if(isset($variation['data']['attributes']))
			{
				foreach($variation['data']['attributes'] as $attribute)
				{
					$attributeValueName = '';

					if(isset($attribute['attributeId']) && isset($attribute['valueId']))
					{
						$attributeValueName = $this->marketAttributeHelperRepositoryContract->getAttributeValueName(
							$attribute['attributeId'],
							$attribute['valueId'],
							'de');
					}

					if(strlen($attributeValueName) > 0)
					{
						$unsortedValues[$attribute['attributeId']] = $attributeValueName;
						$i++;
					}
				}
			}

			$values = $unsortedValues;
		}

		return $values;
	}

	/**
	 * @param array $variation
	 * @param array $filterList
	 * @return bool
	 */
	private function stillActive($variation, $filterList)
	{
		$stillActive = true;

		if(array_key_exists('markets', $filterList))
		{
			$markets = explode(',', $filterList['markets']);
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

		if(array_key_exists('isActive', $filterList))
		{
			if(
				($filterList['isActive'] == 'active' && $variation['data']['variation']['isActive'] != true)
				|| ($filterList['isActive'] == 'inactive' && $variation['data']['variation']['isActive'] != false))
			{
				return false;
			}
		}

		return $stillActive;
	}
}