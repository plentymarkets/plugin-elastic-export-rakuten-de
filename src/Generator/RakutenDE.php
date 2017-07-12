<?php

namespace ElasticExportRakutenDE\Generator;

use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExportRakutenDE\Helper\PriceHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Legacy\Repositories\Item\SalesPrice\SalesPriceSearchRepository;
use Plenty\Modules\DataExchange\Contracts\CSVPluginGenerator;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Market\Helper\Contracts\MarketPropertyHelperRepositoryContract;
use Plenty\Modules\StockManagement\Stock\Contracts\StockRepositoryContract;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;


class RakutenDE extends CSVPluginGenerator
{
    use Loggable;

    const RAKUTEN_DE = 106.00;
    const PROPERTY_TYPE_ENERGY_CLASS       = 'energy_efficiency_class';
    const PROPERTY_TYPE_ENERGY_CLASS_GROUP = 'energy_efficiency_class_group';
    const PROPERTY_TYPE_ENERGY_CLASS_UNTIL = 'energy_efficiency_class_until';
    const TRANSFER_RRP_YES = 1;
    const TRANSFER_OFFER_PRICE_YES = 1;

    /**
     * @var ElasticExportCoreHelper
     */
    private $elasticExportHelper;

    /**
     * @var ArrayHelper
     */
    private $arrayHelper;

    /**
     * @var array
     */
    private $attributeName = array();

    /**
     * @var array
     */
    private $attributeNameCombination = array();

    /**
     * MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository
     */
    private $marketPropertyHelperRepository;
    
    /**
     * @var SalesPriceSearchRepository
     */
    private $salesPriceSearchRepository;

	/**
	 * @var PriceHelper $priceHelper
	 */
    private $priceHelper;

	/**
	 * @var ElasticExportStockHelper $elasticExportStockHelper
	 */
	private $elasticExportStockHelper;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepository;

	/**
	 * RakutenDE constructor.
	 * @param ArrayHelper $arrayHelper
	 * @param MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository
	 * @param SalesPriceSearchRepository $salesPriceSearchRepository
	 * @param PriceHelper $priceHelper
	 * @param VariationSkuRepositoryContract $variationSkuRepository
	 */
    public function __construct(
        ArrayHelper $arrayHelper,
        MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository,
        SalesPriceSearchRepository $salesPriceSearchRepository,
		PriceHelper $priceHelper,
		VariationSkuRepositoryContract $variationSkuRepository
    )
    {
        $this->arrayHelper = $arrayHelper;
        $this->marketPropertyHelperRepository = $marketPropertyHelperRepository;
        $this->salesPriceSearchRepository = $salesPriceSearchRepository;
        $this->priceHelper = $priceHelper;
		$this->variationSkuRepository = $variationSkuRepository;
	}

    /**
     * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
     * @param array $formatSettings
     * @param array $filter
     */
    protected function generatePluginContent($elasticSearch, array $formatSettings = [], array $filter = [])
    {
    	$this->elasticExportStockHelper = pluginApp(ElasticExportStockHelper::class);
        $this->elasticExportHelper = pluginApp(ElasticExportCoreHelper::class);
        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

        $this->setDelimiter(";");

        $this->addCSVContent([
            'id',
            'variante_zu_id',
            'artikelnummer',
            'produkt_bestellbar',
            'produktname',
            'hersteller',
            'beschreibung',
            'variante',
            'variantenwert',
            'isbn_ean',
            'lagerbestand',
            'preis',
            'grundpreis_inhalt',
            'grundpreis_einheit',
            'reduzierter_preis',
            'bezug_reduzierter_preis',
            'mwst_klasse',
            'bestandsverwaltung_aktiv',
            'bild1',
            'bild2',
            'bild3',
            'bild4',
            'bild5',
            'kategorien',
            'lieferzeit',
            'tradoria_kategorie',
            'sichtbar',
            'free_var_1',
            'free_var_2',
            'free_var_3',
            'free_var_4',
            'free_var_5',
            'free_var_6',
            'free_var_7',
            'free_var_8',
            'free_var_9',
            'free_var_10',
            'free_var_11',
            'free_var_12',
            'free_var_13',
            'free_var_14',
            'free_var_15',
            'free_var_16',
            'free_var_17',
            'free_var_18',
            'free_var_19',
            'free_var_20',
            'MPN',
            'bild6',
            'bild7',
            'bild8',
            'bild9',
            'bild10',
            'technical_data',
            'energie_klassen_gruppe',
            'energie_klasse',
            'energie_klasse_bis',
            'energie_klassen_bild',
        ]);

        $currentItemId = null;
        $previousItemId = null;
        $variations = array();
        $lines = 0;
        $limitReached = false;
        $newShard = false;
		$validateOnce = false;

        $startTime = microtime(true);

        if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
        {
            do
            {
                if($limitReached === true)
                {
                    break;
                }

                $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.writtenLines', [
                    'lines written' => $lines,
                ]);

                $esStartTime = microtime(true);

                $resultList = $elasticSearch->execute();

                $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.esDuration', [
                    'Elastic Search duration' => microtime(true) - $esStartTime,
                ]);

                if(count($resultList['error']) > 0)
                {
                    $this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.occurredElasticSearchErrors', [
                        'error message' => $resultList['error'],
                    ]);
                }

                $buildRowStartTime = microtime(true);

                if(is_array($resultList['documents']) && count($resultList['documents']) > 0)
                {
                    foreach($resultList['documents'] as $variation)
                    {
                        if($validateOnce === false)
                        {
                            $validator = pluginApp(GeneratorValidator::class);

                            if($validator instanceof GeneratorValidator)
                            {
                                $isValid = $validator->mainValidator($variation);
                                $validateOnce = true;

                                if($isValid === false)
                                {
                                    return;
                                }
                            }

                        }

                        if($lines == $filter['limit'])
                        {
                            $limitReached = true;
                            break;
                        }

						if($this->elasticExportStockHelper->isFilteredByStock($variation, $filter) === true)
						{
							continue;
						}

                        $lines = $lines +1;

                        // Case first variation
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
                        	try
							{
								$this->buildRows($settings, $variations, $newShard);
							}
							catch(\Throwable $exception)
							{
								$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.buildRowError', [
									'error' => $exception->getMessage(),
									'line' => $exception->getLine(),
								]);
							}

							$newShard = false;
                            $variations = array();
                            $variations[] = $variation;
                            $previousItemId = $variation['data']['item']['id'];
                        }
                    }

                    // Write the last batch of variations
                    if (is_array($variations) && count($variations) > 0)
                    {
                    	try
						{
							$this->buildRows($settings, $variations);
						}
						catch(\Throwable $exception)
						{
							$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.buildRowError', [
								'error' => $exception->getMessage(),
								'line' => $exception->getLine(),
							]);
						}

						$newShard = true;
						unset($variations);
                    }

                    $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.buildRowDuration', [
                        'Build Row duration' => microtime(true) - $buildRowStartTime,
                    ]);
                }

            } while ($elasticSearch->hasNext());
        }

        $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.fileGenerationDuration', [
            'Whole file generation duration' => microtime(true) - $startTime,
        ]);
    }

    /**
     * @param $settings
     * @param array $variations
	 * @param bool $crossShardConnection
     */
    private function buildRows($settings, $variations, $crossShardConnection = false)
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
					$this->buildParentWithChildrenRow($potentialParent, $settings, $this->attributeName);
					$this->buildChildRow($variation, $settings, $attributeValue);
				}

				/**
				 * If it is a new elastic search shard and the first entries are variations from the
				 * last entries of the shard before, the connected variations will be added as children.
				 */
				elseif($crossShardConnection === true)
				{
					$this->buildChildRow($variation, $settings, $attributeValue);
				}

				//isMain can be true or false, this does not matter in this case
				elseif(strlen($attributeValue) == 0 && count($variations) == 1)
				{
					$this->buildParentWithoutChildrenRow($variation, $settings);
				}

				//isMain can be true or false, this does not matter in this case
				elseif(count($variations) == 1 && strlen($attributeValue) > 0)
				{
					$this->buildParentWithChildrenRow($variation, $settings, $this->attributeName);
					$this->buildChildRow($variation, $settings, $attributeValue);
				}

				/**
				 * only if this is the first iteration
				 * && count($variations) > 1
				 * isMain can be true or false, this does not matter in this case
				 */
				elseif($i == 1 && strlen($attributeValue) > 0)
				{
					$this->buildParentWithChildrenRow($variation, $settings, $this->attributeName);
					$this->buildChildRow($variation, $settings, $attributeValue);
				}

				//&& count($variations) > 1
				elseif($variation['data']['variation']['isMain'] === true && strlen($attributeValue) == 0)
				{
					$potentialParent = $variation;
					//check if the coming variations have attributeValues, if not build ParentWithoutChildren, save it into an array
					//make another case where i check the array and act depending on it
				}

				//no attributeValue, not Main and count($variations) > 1
				elseif(strlen($attributeValue) == 0 && $i = 1)
				{
					$this->buildParentWithChildrenRow($variation, $settings, $this->attributeName);
				}

				//count($variations) > 1 && isMain = false
				elseif(strlen($attributeValue) == 0)
				{
					$parentWithoutChildren[] = $variation;
				}

				else
				{
					$this->buildChildRow($variation, $settings, $attributeValue);
				}

                $i++;
            }

            if(count($parentWithoutChildren) > 0)
            {
            	foreach($parentWithoutChildren as $variation)
            	{
					$this->buildParentWithoutChildrenRow($variation, $settings);
				}
			}
        }
    }

    /**
     * @param array $item
     * @param KeyValue $settings
     * @return void
     */
    private function buildParentWithoutChildrenRow($item, KeyValue $settings)
    {
    	$sku = null;
    	$parentSku = null;

    	if(isset($item['data']['skus'][0]['sku']) && strlen($item['data']['skus'][0]['sku']) > 0)
    	{
			$sku = $item['data']['skus'][0]['sku'];
		}

		if(isset($item['data']['skus'][0]['parentSku']) && strlen($item['data']['skus'][0]['parentSku']) > 0)
		{
			$parentSku = $item['data']['skus'][0]['parentSku'];
		}

        $priceList = $this->priceHelper->getPriceList($item, $settings);

		if(isset($priceList['price']) && $priceList['price'] > 0)
		{
			$price = number_format((float)$priceList['price'], 2, '.', '');
		}
		else
		{
			$price = '';
		}

        $vat = $this->getVatClassId($priceList['vatValue']);

        $stockList = $this->getStockList($item);

        $basePriceComponentList = $this->getBasePriceComponentList($item);

        $data = [
            'id'						=> '',
            'variante_zu_id'			=> '',
            'artikelnummer'				=> $this->variationSkuRepository->generateSkuWithParent($item, self::RAKUTEN_DE, (int) $settings->get('marketAccountId'), $sku, $parentSku),
            'produkt_bestellbar'		=> $stockList['variationAvailable'],
            'produktname'				=> $this->elasticExportHelper->getMutatedName($item, $settings, 150),
            'hersteller'				=> $this->elasticExportHelper->getExternalManufacturerName((int)$item['data']['item']['manufacturer']['id']),
            'beschreibung'				=> $this->elasticExportHelper->getMutatedDescription($item, $settings, 5000),
            'variante'					=> isset($this->attributeName[$item['data']['item']['id']]) ? $this->attributeName[$item['data']['item']['id']] : '',
            'variantenwert'				=> '',
            'isbn_ean'					=> $this->elasticExportHelper->getBarcodeByType($item, $settings->get('barcode')),
            'lagerbestand'				=> $stockList['stock'],
            'preis'						=> $price,
            'grundpreis_inhalt'			=> strlen($basePriceComponentList['unit']) ?
                number_format((float)$basePriceComponentList['content'],3,',','') : '',
            'grundpreis_einheit'		=> $basePriceComponentList['unit'],
            'reduzierter_preis'			=> $priceList['reducedPrice'] > 0 ?
                number_format((float)$priceList['reducedPrice'], 2, '.', '') : '',
            'bezug_reduzierter_preis'	=> $priceList['referenceReducedPrice'],
            'mwst_klasse'				=> $vat,
            'bestandsverwaltung_aktiv'	=> $stockList['inventoryManagementActive'],
            'bild1'						=> $this->getImageByPosition($item, 0),
            'bild2'						=> $this->getImageByPosition($item, 1),
            'bild3'						=> $this->getImageByPosition($item, 2),
            'bild4'						=> $this->getImageByPosition($item, 3),
            'bild5'						=> $this->getImageByPosition($item, 4),
            'kategorien'				=> $this->elasticExportHelper->getCategory((int)$item['data']['defaultCategories'][0]['id'], $settings->get('lang'), $settings->get('plentyId')),
            'lieferzeit'				=> $this->elasticExportHelper->getAvailability($item, $settings, false),
            'tradoria_kategorie'		=> $item['data']['item']['rakutenCategoryId'],
            'sichtbar'					=> 1,
            'free_var_1'				=> $item['data']['item']['free1'],
            'free_var_2'				=> $item['data']['item']['free2'],
            'free_var_3'				=> $item['data']['item']['free3'],
            'free_var_4'				=> $item['data']['item']['free4'],
            'free_var_5'				=> $item['data']['item']['free5'],
            'free_var_6'				=> $item['data']['item']['free6'],
            'free_var_7'				=> $item['data']['item']['free7'],
            'free_var_8'				=> $item['data']['item']['free8'],
            'free_var_9'				=> $item['data']['item']['free9'],
            'free_var_10'				=> $item['data']['item']['free10'],
            'free_var_11'				=> $item['data']['item']['free11'],
            'free_var_12'				=> $item['data']['item']['free12'],
            'free_var_13'				=> $item['data']['item']['free13'],
            'free_var_14'				=> $item['data']['item']['free14'],
            'free_var_15'				=> $item['data']['item']['free15'],
            'free_var_16'				=> $item['data']['item']['free16'],
            'free_var_17'				=> $item['data']['item']['free17'],
            'free_var_18'				=> $item['data']['item']['free18'],
            'free_var_19'				=> $item['data']['item']['free19'],
            'free_var_20'				=> $item['data']['item']['free20'],
            'MPN'						=> $item['data']['variation']['model'],
            'bild6'						=> $this->getImageByPosition($item, 5),
            'bild7'						=> $this->getImageByPosition($item, 6),
            'bild8'						=> $this->getImageByPosition($item, 7),
            'bild9'						=> $this->getImageByPosition($item, 8),
            'bild10'					=> $this->getImageByPosition($item, 9),
            'technical_data'			=> $this->elasticExportHelper->getMutatedTechnicalData($item, $settings),
            'energie_klassen_gruppe'	=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS_GROUP),
            'energie_klasse'			=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS),
            'energie_klasse_bis'		=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS_UNTIL),
            'energie_klassen_bild'		=> '',
        ];

        $this->addCSVContent(array_values($data));
    }

    /**
     * @param array $item
     * @param KeyValue $settings
     * @param array $attributeName
     * @return void
     */
    private function buildParentWithChildrenRow($item, KeyValue $settings, array $attributeName)
    {
		$sku = null;
		$parentSku = null;

		if(isset($item['data']['skus'][0]['sku']) && strlen($item['data']['skus'][0]['sku']) > 0)
		{
			$sku = $item['data']['skus'][0]['sku'];
		}

		if(isset($item['data']['skus'][0]['parentSku']) && strlen($item['data']['skus'][0]['parentSku']) > 0)
		{
			$parentSku = $item['data']['skus'][0]['parentSku'];
		}

		$parentSku = $this->variationSkuRepository->generateSkuWithParent($item, self::RAKUTEN_DE, (int) $settings->get('marketAccountId'), $sku, $parentSku, true, true)->parentSku;

        $priceList = $this->priceHelper->getPriceList($item, $settings);

        $vat = $this->getVatClassId($priceList['vatValue']);

        $stockList = $this->getStockList($item);

        $data = [
            'id'						=> '#'.$item['data']['item']['id'],
            'variante_zu_id'			=> '',
            'artikelnummer'				=> $parentSku,
            'produkt_bestellbar'		=> '',
            'produktname'				=> $this->elasticExportHelper->getMutatedName($item, $settings, 150),
            'hersteller'				=> $this->elasticExportHelper->getExternalManufacturerName((int)$item['data']['item']['manufacturer']['id']),
            'beschreibung'				=> $this->elasticExportHelper->getMutatedDescription($item, $settings, 5000),
            'variante'					=> $attributeName[$item['data']['item']['id']],
            'variantenwert'				=> '',
            'isbn_ean'					=> '',
            'lagerbestand'				=> '',
            'preis'						=> '',
            'grundpreis_inhalt'			=> '',
            'grundpreis_einheit'		=> '',
            'reduzierter_preis'			=> '',
            'bezug_reduzierter_preis'	=> '',
            'mwst_klasse'				=> $vat,
            'bestandsverwaltung_aktiv'	=> $stockList['inventoryManagementActive'],
            'bild1'						=> $this->getImageByPosition($item, 0),
            'bild2'						=> $this->getImageByPosition($item, 1),
            'bild3'						=> $this->getImageByPosition($item, 2),
            'bild4'						=> $this->getImageByPosition($item, 3),
            'bild5'						=> $this->getImageByPosition($item, 4),
            'kategorien'				=> $this->elasticExportHelper->getCategory((int)$item['data']['defaultCategories'][0]['id'], $settings->get('lang'), $settings->get('plentyId')),
            'lieferzeit'				=> '',
            'tradoria_kategorie'		=> $item['data']['item']['rakutenCategoryId'],
            'sichtbar'					=> 1,
            'free_var_1'				=> $item['data']['item']['free1'],
            'free_var_2'				=> $item['data']['item']['free2'],
            'free_var_3'				=> $item['data']['item']['free3'],
            'free_var_4'				=> $item['data']['item']['free4'],
            'free_var_5'				=> $item['data']['item']['free5'],
            'free_var_6'				=> $item['data']['item']['free6'],
            'free_var_7'				=> $item['data']['item']['free7'],
            'free_var_8'				=> $item['data']['item']['free8'],
            'free_var_9'				=> $item['data']['item']['free9'],
            'free_var_10'				=> $item['data']['item']['free10'],
            'free_var_11'				=> $item['data']['item']['free11'],
            'free_var_12'				=> $item['data']['item']['free12'],
            'free_var_13'				=> $item['data']['item']['free13'],
            'free_var_14'				=> $item['data']['item']['free14'],
            'free_var_15'				=> $item['data']['item']['free15'],
            'free_var_16'				=> $item['data']['item']['free16'],
            'free_var_17'				=> $item['data']['item']['free17'],
            'free_var_18'				=> $item['data']['item']['free18'],
            'free_var_19'				=> $item['data']['item']['free19'],
            'free_var_20'				=> $item['data']['item']['free20'],
            'MPN'						=> $item['data']['variation']['model'],
            'bild6'						=> $this->getImageByPosition($item, 5),
            'bild7'						=> $this->getImageByPosition($item, 6),
            'bild8'						=> $this->getImageByPosition($item, 7),
            'bild9'						=> $this->getImageByPosition($item, 8),
            'bild10'					=> $this->getImageByPosition($item, 9),
            'technical_data'			=> $this->elasticExportHelper->getMutatedTechnicalData($item, $settings),
            'energie_klassen_gruppe'	=> '',
            'energie_klasse'			=> '',
            'energie_klasse_bis'		=> '',
            'energie_klassen_bild'		=> '',
        ];

        $this->addCSVContent(array_values($data));
    }

    /**
     * @param array $item
     * @param KeyValue $settings
     * @param string $attributeValue
     * @return void
     */
    private function buildChildRow($item, KeyValue $settings, string $attributeValue = '')
    {

		$sku = null;
		$parentSku = null;

		if(isset($item['data']['skus'][0]['sku']) && strlen($item['data']['skus'][0]['sku']) > 0)
		{
			$sku = $item['data']['skus'][0]['sku'];
		}

		if(isset($item['data']['skus'][0]['parentSku']) && strlen($item['data']['skus'][0]['parentSku']) > 0)
		{
			$parentSku = $item['data']['skus'][0]['parentSku'];
		}

        $stockList = $this->getStockList($item);

        $priceList = $this->priceHelper->getPriceList($item, $settings);

        if(isset($priceList['price']) && $priceList['price'] > 0)
        {
        	$price = number_format((float)$priceList['price'], 2, '.', '');
		}
		else
		{
			$price = '';
		}

        $basePriceComponentList = $this->getBasePriceComponentList($item);

        $data = [
            'id'						=> '',
            'variante_zu_id'			=> '#'.$item['data']['item']['id'],
            'artikelnummer'				=> $this->variationSkuRepository->generateSkuWithParent($item, self::RAKUTEN_DE, (int) $settings->get('marketAccountId'), $sku, $parentSku),
            'produkt_bestellbar'		=> $stockList['variationAvailable'],
            'produktname'				=> '',
            'hersteller'				=> '',
            'beschreibung'				=> '',
            'variante'					=> '',
            'variantenwert'				=> $attributeValue,
            'isbn_ean'					=> $this->elasticExportHelper->getBarcodeByType($item, $settings->get('barcode')),
            'lagerbestand'				=> $stockList['stock'],
            'preis'						=> $price,
            'grundpreis_inhalt'			=> strlen($basePriceComponentList['unit']) ?
                number_format((float)$basePriceComponentList['content'],3,',','') : '',
            'grundpreis_einheit'		=> $basePriceComponentList['unit'],
            'reduzierter_preis'			=> $priceList['reducedPrice'] > 0 ?
                number_format((float)$priceList['reducedPrice'], 2, '.', '') : '',
            'bezug_reduzierter_preis'	=> $priceList['referenceReducedPrice'],
            'mwst_klasse'				=> '',
            'bestandsverwaltung_aktiv'	=> '',
            'bild1'						=> '',
            'bild2'						=> '',
            'bild3'						=> '',
            'bild4'						=> '',
            'bild5'						=> '',
            'kategorien'				=> '',
            'lieferzeit'				=> $this->elasticExportHelper->getAvailability($item, $settings, false),
            'tradoria_kategorie'		=> '',
            'sichtbar'					=> 1,
            'free_var_1'				=> $item['data']['item']['free1'],
            'free_var_2'				=> $item['data']['item']['free2'],
            'free_var_3'				=> $item['data']['item']['free3'],
            'free_var_4'				=> $item['data']['item']['free4'],
            'free_var_5'				=> $item['data']['item']['free5'],
            'free_var_6'				=> $item['data']['item']['free6'],
            'free_var_7'				=> $item['data']['item']['free7'],
            'free_var_8'				=> $item['data']['item']['free8'],
            'free_var_9'				=> $item['data']['item']['free9'],
            'free_var_10'				=> $item['data']['item']['free10'],
            'free_var_11'				=> $item['data']['item']['free11'],
            'free_var_12'				=> $item['data']['item']['free12'],
            'free_var_13'				=> $item['data']['item']['free13'],
            'free_var_14'				=> $item['data']['item']['free14'],
            'free_var_15'				=> $item['data']['item']['free15'],
            'free_var_16'				=> $item['data']['item']['free16'],
            'free_var_17'				=> $item['data']['item']['free17'],
            'free_var_18'				=> $item['data']['item']['free18'],
            'free_var_19'				=> $item['data']['item']['free19'],
            'free_var_20'				=> $item['data']['item']['free20'],
            'MPN'						=> $item['data']['variation']['model'],
            'bild6'						=> '',
            'bild7'						=> '',
            'bild8'						=> '',
            'bild9'						=> '',
            'bild10'					=> '',
            'technical_data'			=> '',
            'energie_klassen_gruppe'	=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS_GROUP),
            'energie_klasse'			=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS),
            'energie_klasse_bis'		=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS_UNTIL),
            'energie_klassen_bild'		=> '',
        ];

        $this->addCSVContent(array_values($data));
    }

    /**
	 * Returns the URL of an image depending on the configured position.
	 *
	 * Fallback in case of no found image with position x to entry x in list.
	 *
     * @param array $item
     * @param int $position
     * @return string
     */
    private function getImageByPosition($item, int $position):string
    {
		if(is_array($item['data']['images']['all']) && count($item['data']['images']['all']) > 0)
		{
			$count = 0;
			$images = [];

			foreach($item['data']['images']['all'] as $image)
			{
				if(!array_key_exists($image['position'], $images))
				{
					$images[$image['position']] = $image;
				}
				else
				{
					$count++;
					$images[$image['position'].'_'.$count] = $image;
				}
			}

			// sort by key
			ksort($images);
			$images = array_values($images);

			if(isset($images[$position]))
			{
				return (string)$this->elasticExportHelper->getImageUrlBySize($images[$position]);
			}
		}

		return '';
    }

    /**
     * Returns the unit, if there is any unit configured, which is allowed
     * for the Rakuten.de API.
     *
     * @param  array   $item
     * @return string
     */
    private function getUnit($item):string
    {
        switch((int) $item['data']['unit']['id'])
        {
            case '32':
                return 'ml'; // Milliliter
            case '5':
                return 'l'; // Liter
            case '3':
                return 'g'; // Gramm
            case '2':
                return 'kg'; // Kilogramm
            case '51':
                return 'cm'; // Zentimeter
            case '31':
                return 'm'; // Meter
            case '38':
                return 'm²'; // Quadratmeter
            default:
                return '';
        }
    }

    /**
     * Get id for vat
     * @param int $vatValue
     * @return int
     */
    private function getVatClassId($vatValue):int
    {
        $vat = $vatValue;
        if($vat == '10,7')
        {
            $vat = 4;
        }
        else if($vat == '7')
        {
            $vat = 2;
        }
        else if($vat == '0')
        {
            $vat = 3;
        }
        else
        {
            //bei anderen Steuersaetzen immer 19% nehmen
            $vat = 1;
        }
        return $vat;
    }

    /**
     * Get item characters that match referrer from settings and a given component id.
     * @param  array    $item
     * @param  float    $marketId
     * @param  string   $externalComponent
     * @return string
     */
    private function getItemPropertyByExternalComponent($item ,float $marketId, $externalComponent):string
    {
        $marketProperties = $this->marketPropertyHelperRepository->getMarketProperty($marketId);

        if(is_array($item['data']['properties']) && count($item['data']['properties']) > 0)
        {
            foreach($item['data']['properties'] as $property)
            {
                foreach($marketProperties as $marketProperty)
                {
                    if(array_key_exists('id', $property['property']))
                    {
                        if(is_array($marketProperty) && count($marketProperty) > 0 && $marketProperty['character_item_id'] == $property['property']['id'])
                        {
                            if (strlen($externalComponent) > 0 && strpos($marketProperty['external_component'], $externalComponent) !== false)
                            {
                                $list = explode(':', $marketProperty['external_component']);
                                if (isset($list[1]) && strlen($list[1]) > 0)
                                {
                                    return $list[1];
                                }
                            }
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * Get necessary components to enable Rakuten to calculate a base price for the variation
     * @param array $item
     * @return array
     */
    private function getBasePriceComponentList($item):array
    {
        $unit = $this->getUnit($item);
        $content = (float)$item['data']['unit']['content'];
        $convertBasePriceContentTag = $this->elasticExportHelper->getConvertContentTag($content, 3);
        if ($convertBasePriceContentTag == true && strlen($unit))
        {
            $content = $this->elasticExportHelper->getConvertedBasePriceContent($content, $unit);
            $unit = $this->elasticExportHelper->getConvertedBasePriceUnit($unit);
        }
        return array(
            'content'   =>  $content,
            'unit'      =>  $unit,
        );
    }

    /**
     * Get all informations that depend on stock settings and stock volume
     * (inventoryManagementActive, $variationAvailable, $stock)
     * @param $item
     * @return array
     */
    private function getStockList($item):array
    {
        $stockNet = 0;
        $stockRepositoryContract = pluginApp(StockRepositoryContract::class);

        if($stockRepositoryContract instanceof StockRepositoryContract)
        {
            $stockRepositoryContract->setFilters(['variationId' => $item['id']]);
            $stockResult = $stockRepositoryContract->listStockByWarehouseType('sales', ['stockNet'], 1, 1);

            if($stockResult instanceof PaginatedResult)
			{
                $stockList = $stockResult->getResult();
                foreach($stockList as $stock)
                {
                    $stockNet = $stock->stockNet;
                    break;
                }
			}
            else
			{
				$stockNet = 0;
			}
        }

        $inventoryManagementActive = 0;
        $variationAvailable = 0;
        $stock = 0;

        if($item['data']['variation']['stockLimitation'] == 2)
        {
            $variationAvailable = 1;
            $inventoryManagementActive = 0;
            $stock = 999;
        }
        elseif($item['data']['variation']['stockLimitation'] == 1 && $stockNet > 0)
        {
            $variationAvailable = 1;
            $inventoryManagementActive = 1;
            if($stockNet > 999)
            {
                $stock = 999;
            }
            else
            {
                $stock = $stockNet;
            }
        }
        elseif($item['data']['variation']['stockLimitation'] == 0)
        {
            $variationAvailable = 1;
            $inventoryManagementActive = 0;
            if($stockNet > 999)
            {
                $stock = 999;
            }
            else
            {
                if($stockNet > 0)
                {
                    $stock = $stockNet;
                }
                else
                {
                    $stock = 999;
                }
            }
        }

        return array (
            'stock'                     =>  $stock,
            'variationAvailable'        =>  $variationAvailable,
            'inventoryManagementActive' =>  $inventoryManagementActive,
        );

    }
}