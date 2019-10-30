<?php

namespace ElasticExportRakutenDE\Generator;

use ElasticExport\Helper\AbstractElasticExportHelper;
use ElasticExport\Helper\ElasticExportCategoryHelper;
use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExport\Helper\ElasticExportItemHelper;
use ElasticExport\Services\FiltrationService;
use ElasticExportRakutenDE\ElasticExportRakutenDEServiceProvider;
use ElasticExportRakutenDE\Helper\AttributeHelper;
use ElasticExportRakutenDE\Helper\PriceHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use ElasticExportRakutenDE\Helper\SkuHelper;
use ElasticExportRakutenDE\Helper\StockHelper;
use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Legacy\Repositories\Item\SalesPrice\SalesPriceSearchRepository;
use Plenty\Modules\DataExchange\Contracts\CSVPluginGenerator;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Market\Helper\Contracts\MarketPropertyHelperRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

class RakutenDE extends CSVPluginGenerator
{
    use Loggable;

    const RAKUTEN_DE = ElasticExportRakutenDEServiceProvider::ORDER_REFERRER_RAKUTEN_DE;
    const PROPERTY_TYPE_ENERGY_CLASS       = 'energy_efficiency_class';
    const PROPERTY_TYPE_ENERGY_CLASS_GROUP = 'energy_efficiency_class_group';
    const PROPERTY_TYPE_ENERGY_CLASS_UNTIL = 'energy_efficiency_class_until';
    const TRANSFER_RRP_YES = 1;
    const TRANSFER_OFFER_PRICE_YES = 1;

    /**
     * @var FiltrationService
     */
    private $filtrationService;

    /**
     * @var ElasticExportCoreHelper
     */
    private $elasticExportHelper;

	/**
	 * @var ElasticExportItemHelper
	 */
	private $elasticExportItemHelper;

    /**
     * @var ArrayHelper
     */
    private $arrayHelper;

	/**
	 * @var PriceHelper $priceHelper
	 */
	private $priceHelper;

	/**
	 * @var ElasticExportStockHelper $elasticExportStockHelper
	 */
	private $elasticExportStockHelper;

	/**
	 * @var MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository
	 */
	private $marketPropertyHelperRepository;

	/**
	 * @var ElasticExportCategoryHelper $elasticExportCategoryHelper
	 */
	private $elasticExportCategoryHelper;

	/**
	 * @var SalesPriceSearchRepository
	 */
	private $salesPriceSearchRepository;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepository;

	/**
	 * @var ConfigRepository
	 */
	private $configRepository;

    /**
     * @var AttributeHelper
     */
	private $attributeHelper;

    /**
     * @var array
     */
    private $imageCache = [];

	/**
	 * @var string
	 */
	private $parentSku = '';

	/**
	 * @var StockHelper
	 */
	private $stockHelper;

	/**
	 * @var array
	 */
	private $errorBatch = [];

	/**
	 * @var int
	 */
	private $errorIterator = 0;

    /**
     * @var SkuHelper
     */
    private $skuHelper;

    /**
     * RakutenDE constructor.
     *
     * @param ArrayHelper $arrayHelper
     * @param MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository
     * @param SalesPriceSearchRepository $salesPriceSearchRepository
     * @param PriceHelper $priceHelper
     * @param VariationSkuRepositoryContract $variationSkuRepository
     * @param ConfigRepository $configRepository
     * @param AttributeHelper $attributeHelper
     * @param StockHelper $stockHelper
     * @param SkuHelper $skuHelper
     */
    public function __construct(
        ArrayHelper $arrayHelper,
        MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository,
        SalesPriceSearchRepository $salesPriceSearchRepository,
		PriceHelper $priceHelper,
		VariationSkuRepositoryContract $variationSkuRepository,
		ConfigRepository $configRepository,
		AttributeHelper $attributeHelper,
		StockHelper $stockHelper,
        SkuHelper $skuHelper
    )
    {
        $this->arrayHelper = $arrayHelper;
        $this->marketPropertyHelperRepository = $marketPropertyHelperRepository;
        $this->salesPriceSearchRepository = $salesPriceSearchRepository;
        $this->priceHelper = $priceHelper;
		$this->variationSkuRepository = $variationSkuRepository;
		$this->configRepository = $configRepository;
		$this->attributeHelper = $attributeHelper;
		$this->stockHelper = $stockHelper;
        $this->skuHelper = $skuHelper;
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
        $this->elasticExportItemHelper = pluginApp(ElasticExportItemHelper::class, [1 => true]);
        $this->elasticExportCategoryHelper = pluginApp(ElasticExportCategoryHelper::class);

        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');
		$this->filtrationService = pluginApp(FiltrationService::class, ['settings' => $settings, 'filterSettings' => $filter]);

		$this->stockHelper->setAdditionalStockInformation($settings);

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
		$validateOnce = false;
		$shardIterator = 0;

        $startTime = microtime(true);

        if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
        {
        	$elasticSearch->setNumberOfDocumentsPerShard(250);

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

                $shardIterator++;

                //log the amount of the elasticsearch result once
				if($shardIterator == 1)
				{
					$this->getLogger(__METHOD__)->addReference('total', (int)$resultList['total'])->info('ElasticExportRakutenDE::log.esResultAmount');
				}

                $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.esDuration', [
                    'Elastic Search duration' => microtime(true) - $esStartTime,
                ]);

                if(count($resultList['error']) > 0)
                {
                    $this->getLogger(__METHOD__)->addReference('failedShard', $shardIterator)->error('ElasticExportRakutenDE::log.occurredElasticSearchErrors', [
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

						if($this->filtrationService->filter($variation) === true)
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
								$this->buildRows($settings, $variations);
							}
							catch(\Throwable $exception)
							{
								$this->errorBatch['rowError'][] = [
									'error' => $exception->getMessage(),
									'file' => $exception->getFile(),
                                    'line' => $exception->getLine(),
                                    'stack trace' => $exception->getTrace(),
								];

								$this->errorIterator++;

								if($this->errorIterator == 100)
								{
									$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.buildRowError', [
										'errorList'	=> $this->errorBatch['rowError']
									]);

									$this->errorIterator = 0;
								}
							}

                            $variations = array();
                            $variations[] = $variation;
                            
                            $this->attributeHelper->resetAttributeCache();

                            $previousItemId = $variation['data']['item']['id'];
                        }
                    }

                    $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.buildRowDuration', [
                        'Build Row duration' => microtime(true) - $buildRowStartTime,
                    ]);
                }

                if($shardIterator % 4 == 0) {
                    $this->clearHelperCache();
                }
            } while ($elasticSearch->hasNext());
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
				$this->errorBatch['rowError'][] = [
					'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
					'line' => $exception->getLine(),
                    'stack trace' => $exception->getTrace(),
				];

				$this->errorIterator++;

				if($this->errorIterator == 100)
				{
					$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.buildRowError', [
						'errorList'	=> $this->errorBatch['rowError']
					]);

					$this->errorIterator = 0;
				}
			}

			unset($variations);
		}

		$this->skuHelper->finish();

		if(is_array($this->errorBatch) && count($this->errorBatch['rowError']))
		{
			$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.buildRowError', [
				'errorList'	=> $this->errorBatch['rowError']
			]);

			$this->errorIterator = 0;
		}

        $this->getLogger(__METHOD__)->debug('ElasticExportRakutenDE::log.fileGenerationDuration', [
            'Whole file generation duration' => microtime(true) - $startTime,
        ]);
    }

    /**
     * @param $settings
     * @param array $variations
     */
    private function buildRows($settings, $variations)
    {
        if (is_array($variations) && count($variations)) {
            $variations = $this->attributeHelper->getPreparedVariantItem($variations, $settings);

            foreach($variations as $key => $variation)
            {
                $attributeValue = $this->attributeHelper->getRakutenAttributeValueString($variation, $settings);
                
                if (strlen($attributeValue)) {
                    if ($key == 0) {
                        $this->buildParentWithChildrenRow($variation, $settings);
                    }
                    
                    $this->buildChildRow($variation, $settings, $attributeValue);
                } else {
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
		$this->parentSku = '';

        $priceList = $this->priceHelper->getPriceList($item, $settings);

		if(isset($priceList['price']) && $priceList['price'] > 0)
		{
			$price = number_format((float)$priceList['price'], 2, '.', '');
		}
		else
		{
			$price = '';
		}

	    $skuData = $this->setSku($item, $settings);

	    if(is_null($skuData))
	    {
		    return;
	    }

        $vat = $this->getVatClassId($priceList['vatValue']);

        $stockList = $this->stockHelper->getStockList($item);

        $basePriceComponentList = $this->getBasePriceComponentList($item);

        $categories = $this->getCategories($item, $settings);

        $data = [
            'id'						=> '',
            'variante_zu_id'			=> '',
            'artikelnummer'				=> $skuData->sku,
            'produkt_bestellbar'		=> $stockList['variationAvailable'],
            'produktname'				=> $this->elasticExportHelper->getMutatedName($item, $settings, 150),
            'hersteller'				=> $this->elasticExportHelper->getExternalManufacturerName((int)$item['data']['item']['manufacturer']['id']),
            'beschreibung'				=> $this->elasticExportHelper->getMutatedDescription($item, $settings, 5000),
            'variante'					=> isset($this->attributeHelper->getAttributeNames()[$variation['data']['item']['id']]) ? $this->attributeHelper->getAttributeNames()[$variation['data']['item']['id']] : '',
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
            'bild1'						=> $this->getImageByPosition($item, $settings, 0),
            'bild2'						=> $this->getImageByPosition($item, $settings, 1),
            'bild3'						=> $this->getImageByPosition($item, $settings, 2),
            'bild4'						=> $this->getImageByPosition($item, $settings, 3),
            'bild5'						=> $this->getImageByPosition($item, $settings, 4),
            'kategorien'				=> $categories,
            'lieferzeit'				=> $this->elasticExportHelper->getAvailability($item, $settings, false),
            'tradoria_kategorie'		=> $item['data']['item']['rakutenCategoryId'],
            'sichtbar'					=> 1,
            'free_var_1'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 1),
            'free_var_2'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 2),
            'free_var_3'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 3),
            'free_var_4'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 4),
            'free_var_5'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 5),
            'free_var_6'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 6),
            'free_var_7'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 7),
            'free_var_8'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 8),
            'free_var_9'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 9),
            'free_var_10'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 10),
            'free_var_11'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 11),
            'free_var_12'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 12),
            'free_var_13'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 13),
            'free_var_14'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 14),
            'free_var_15'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 15),
            'free_var_16'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 16),
            'free_var_17'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 17),
            'free_var_18'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 18),
            'free_var_19'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 19),
            'free_var_20'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 20),
            'MPN'						=> $item['data']['variation']['model'],
            'bild6'						=> $this->getImageByPosition($item, $settings, 5),
            'bild7'						=> $this->getImageByPosition($item, $settings, 6),
            'bild8'						=> $this->getImageByPosition($item, $settings, 7),
            'bild9'						=> $this->getImageByPosition($item, $settings, 8),
            'bild10'					=> $this->getImageByPosition($item, $settings, 9),
            'technical_data'			=> $this->elasticExportHelper->getMutatedTechnicalData($item, $settings),
            'energie_klassen_gruppe'	=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS_GROUP),
            'energie_klasse'			=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS),
            'energie_klasse_bis'		=> $this->getItemPropertyByExternalComponent($item, self::RAKUTEN_DE, self::PROPERTY_TYPE_ENERGY_CLASS_UNTIL),
            'energie_klassen_bild'		=> $this->getImageByPosition($item, $settings, 'energyLabel'),
        ];

        $this->addCSVContent(array_values($data));
    }

    /**
     * @param array $item
     * @param KeyValue $settings
     * @return void
     */
    private function buildParentWithChildrenRow($item, KeyValue $settings)
    {
	    $this->parentSku = '';

	    $skuData = $this->setSku($item, $settings);

	    if(is_null($skuData))
	    {
		    return;
	    }

	    $this->parentSku = $skuData->parentSku;

        $priceList = $this->priceHelper->getPriceList($item, $settings);

        $vat = $this->getVatClassId($priceList['vatValue']);

        $stockList = $this->stockHelper->getStockList($item);

		$categories = $this->getCategories($item, $settings);

        $data = [
            'id'						=> '#'.$item['data']['item']['id'],
            'variante_zu_id'			=> '',
            'artikelnummer'				=> $skuData->parentSku,
            'produkt_bestellbar'		=> '',
            'produktname'				=> $this->elasticExportHelper->getMutatedName($item, $settings, 150),
            'hersteller'				=> $this->elasticExportHelper->getExternalManufacturerName((int)$item['data']['item']['manufacturer']['id']),
            'beschreibung'				=> $this->elasticExportHelper->getMutatedDescription($item, $settings, 5000),
            'variante'					=> $this->attributeHelper->getAttributeNames()[$item['data']['item']['id']],
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
            'bild1'						=> $this->getImageByPosition($item, $settings, 0),
            'bild2'						=> $this->getImageByPosition($item, $settings, 1),
            'bild3'						=> $this->getImageByPosition($item, $settings, 2),
            'bild4'						=> $this->getImageByPosition($item, $settings, 3),
            'bild5'						=> $this->getImageByPosition($item, $settings, 4),
            'kategorien'				=> $categories,
            'lieferzeit'				=> '',
            'tradoria_kategorie'		=> $item['data']['item']['rakutenCategoryId'],
            'sichtbar'					=> 1,
            'free_var_1'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 1),
            'free_var_2'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 2),
            'free_var_3'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 3),
            'free_var_4'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 4),
            'free_var_5'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 5),
            'free_var_6'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 6),
            'free_var_7'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 7),
            'free_var_8'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 8),
            'free_var_9'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 9),
            'free_var_10'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 10),
            'free_var_11'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 11),
            'free_var_12'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 12),
            'free_var_13'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 13),
            'free_var_14'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 14),
            'free_var_15'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 15),
            'free_var_16'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 16),
            'free_var_17'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 17),
            'free_var_18'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 18),
            'free_var_19'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 19),
            'free_var_20'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 20),
            'MPN'						=> $item['data']['variation']['model'],
            'bild6'						=> $this->getImageByPosition($item, $settings, 5),
            'bild7'						=> $this->getImageByPosition($item, $settings, 6),
            'bild8'						=> $this->getImageByPosition($item, $settings, 7),
            'bild9'						=> $this->getImageByPosition($item, $settings, 8),
            'bild10'					=> $this->getImageByPosition($item, $settings, 9),
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
	    $skuData = $this->setSku($item, $settings);

	    if(is_null($skuData))
	    {
		    return;
	    }

        $stockList = $this->stockHelper->getStockList($item);

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
            'artikelnummer'				=> $skuData->sku,
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
            'free_var_1'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 1),
            'free_var_2'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 2),
            'free_var_3'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 3),
            'free_var_4'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 4),
            'free_var_5'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 5),
            'free_var_6'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 6),
            'free_var_7'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 7),
            'free_var_8'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 8),
            'free_var_9'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 9),
            'free_var_10'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 10),
            'free_var_11'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 11),
            'free_var_12'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 12),
            'free_var_13'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 13),
            'free_var_14'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 14),
            'free_var_15'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 15),
            'free_var_16'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 16),
            'free_var_17'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 17),
            'free_var_18'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 18),
            'free_var_19'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 19),
            'free_var_20'				=> $this->elasticExportItemHelper->getFreeFields($item['data']['item']['id'], 20),
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
            'energie_klassen_bild'		=> $this->getImageByPosition($item, $settings, 'energyLabel'),
        ];

        $this->addCSVContent(array_values($data));
    }

    /**
     * Returns the URL of an image depending on the configured position.
     *
     * Fallback in case of no found image with position x to entry x in list.
     *
     * @param array $item
     * @param KeyValue $settings
     * @param int|string $position
     * @return string
     */
    private function getImageByPosition($item, $settings, $position):string
    {
        $itemId = $item['data']['item']['id'];

        if(!isset($this->imageCache[$itemId]))
        {
            $this->imageCache = [];

            if(is_array($item['data']['images']['all']) && count($item['data']['images']['all']) > 0)
            {
                $count = 0;
                $images = [];
                $energyLabel = '';

                foreach($item['data']['images']['all'] as $image)
                {

                    if(!array_key_exists($image['position'], $images)
                        && $settings->get('energyLabel') != ''
                        && $image['position'] == $settings->get('energyLabel'))
                    {
                        $energyLabel = $image;
                    }
                    elseif(!array_key_exists($image['position'], $images))
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
                $this->imageCache[$itemId] = array_values($images);
                $this->imageCache[$itemId]['energyLabel'] = $energyLabel;
            }
        }

        if(isset($this->imageCache[$itemId][$position]))
        {
            return (string)$this->elasticExportHelper->getImageUrlBySize($this->imageCache[$itemId][$position]);
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
     * Get necessary components to enable Rakuten to calculate a base price for the variation.
	 *
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
	 * Get all category paths in one string, separated by || .
	 *
	 * @param $variation
	 * @param $settings
	 *
	 * @return string
	 */
    private function getCategories($variation, $settings)
	{
		$allCategories = '';

		foreach($variation['data']['ids']['categories']['branches'] as $categoryId)
		{
			$categoryPath = $this->elasticExportCategoryHelper->getCategoryPath($categoryId, $settings, '>');

			if(strlen($categoryPath) > 0)
			{
				if(strlen($allCategories) > 0)
				{
					$allCategories = $allCategories.' || '.$categoryPath;
				}
				else
				{
					$allCategories = $categoryPath;
				}
			}
		}

		return $allCategories;
	}

	/**
	 * @param array $variation
	 * @param KeyValue $settings
	 * @return array|null|VariationSku
	 */
	private function setSku(array $variation, KeyValue $settings)
	{
		$parentSku = null;

		if(strlen($this->parentSku))
		{
			$parentSku = $this->parentSku;
		}

		$parentPrefix = $this->configRepository->get('ElasticExportRakutenDE.parent_sku.prefix');
		$parentSuffix = $this->configRepository->get('ElasticExportRakutenDE.parent_sku.suffix');

		$skuDataList = $this->variationSkuRepository->search([
			'variationId' => $variation['id'],
			'marketId' => self::RAKUTEN_DE,
			'accountId' => (int) $settings->get('marketAccountId')
		]);

		if(count($skuDataList))
		{
			foreach($skuDataList as $skuData)
			{
				if(strlen($skuData->sku) == 0)
				{
					$skuData->sku = $variation['id'];
				}

				if(strlen($skuData->parentSku) == 0)
				{
					if(!is_null($parentSku))
					{
						$skuData->parentSku = $parentSku;
					}
					else
					{
						$skuData->parentSku = $parentPrefix . $variation['data']['item']['id'] . $parentSuffix;
					}
				}

				$skuData->exportedAt = date("Y-m-d H:i:s");
                $skuData->status = VariationSku::MARKET_STATUS_ACTIVE;

				$skuData = $this->variationSkuRepository->update($skuData->toArray(), $skuData->id);

				return $skuData;
			}
		}
		else
		{
			if(is_null($parentSku))
			{
				$parentSku = $parentPrefix . $variation['data']['item']['id'] . $parentSuffix;
			}

			$skuData = [
				'variationId' => $variation['id'],
				'marketId' => self::RAKUTEN_DE,
				'accountId' => (int) $settings->get('marketAccountId'),
				'initialSku' => $variation['id'],
				'sku' => $variation['id'],
				'parentSku' => $parentSku,
                'status' => VariationSku::MARKET_STATUS_ACTIVE,
				'createdAt' => date("Y-m-d H:i:s"),
				'exportedAt' => date("Y-m-d H:i:s")
			];

			$skuData = $this->variationSkuRepository->create($skuData);

			return $skuData;
		}

		return null;
	}

    /**
     * Clears cache of helpers which supports the clearCache() function.
     * @see AbstractElasticExportHelper
     */
    private function clearHelperCache()
    {
        $this->elasticExportCategoryHelper->clearCache();
        $this->elasticExportItemHelper->clearCache();
    }
}
