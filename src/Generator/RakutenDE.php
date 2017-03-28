<?php

namespace ElasticExportRakutenDE\Generator;

use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Legacy\Repositories\Item\SalesPrice\SalesPriceSearchRepository;
use Plenty\Modules\DataExchange\Contracts\CSVPluginGenerator;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Market\Helper\Contracts\MarketPropertyHelperRepositoryContract;
use Plenty\Modules\StockManagement\Stock\Contracts\StockRepositoryContract;
use Plenty\Plugin\Log\Loggable;


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
     * RakutenDE constructor.
     * @param ArrayHelper $arrayHelper
     * @param MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository
     */
    public function __construct(
        ArrayHelper $arrayHelper,
        MarketPropertyHelperRepositoryContract $marketPropertyHelperRepository
    )
    {
        $this->arrayHelper = $arrayHelper;
        $this->marketPropertyHelperRepository = $marketPropertyHelperRepository;
    }

    /**
     * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
     * @param array $formatSettings
     * @param array $filter
     */
    protected function generatePluginContent($elasticSearch, array $formatSettings = [], array $filter = [])
    {
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

                $validateOnce = false;
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
                        /**
                         * If the stock filter is set, this will sort out all variations
                         * not matching the filter.
                         */
                        if(array_key_exists('variationStock.netPositive' ,$filter))
                        {
                            $stockList = $this->getStockList($variation);
                            if($stockList['stock'] <= 0)
                            {
                                continue;
                            }
                        }
                        elseif(array_key_exists('variationStock.isSalable' ,$filter))
                        {
                            if(count($filter['variationStock.isSalable']['stockLimitation']) == 2)
                            {
                                if($variation['data']['variation']['stockLimitation'] != 0 || $variation['data']['variation']['stockLimitation'] != 2)
                                {
                                    $stockList = $this->getStockList($variation);
                                    if($stockList['stock'] <= 0)
                                    {
                                        continue;
                                    }
                                }
                            }
                            else
                            {
                                if($variation['data']['variation']['stockLimitation'] != $filter['variationStock.isSalable']['stockLimitation'][0])
                                {
                                    $stockList = $this->getStockList($variation);
                                    if($stockList['stock'] <= 0)
                                    {
                                        continue;
                                    }
                                }
                            }
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
                            $this->buildRows($settings, $variations);
                            $variations = array();
                            $variations[] = $variation;
                            $previousItemId = $variation['data']['item']['id'];
                        }
                    }

                    // Write the last batch of variations
                    if (is_array($variations) && count($variations) > 0)
                    {
                        $this->buildRows($settings, $variations);
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
     */
    private function buildRows($settings, $variations)
    {
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
                    if(is_array($variation['data']['attributes']) && count($variation['data']['attributes']) > 0)
                    {
                        foreach ($variation['data']['attributes'] as $attribute)
                        {
                            if(array_key_exists('attributeId', $attribute))
                            {
                                $this->attributeNameCombination[$variation['data']['item']['id']][] = $attribute['attributeId'];
                            }
                        }
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

                if(count($variations) == 1)
                {
                    $this->buildParentWithoutChildrenRow($variation, $settings);
                }
                elseif($variation['data']['variation']['isMain'] === false && $i == 1)
                {
                    $this->buildParentWithChildrenRow($variation, $settings, $this->attributeName);
                    $this->buildChildRow($variation, $settings, $attributeValue);
                }
                elseif($variation['data']['variation']['isMain'] === true && strlen($attributeValue) > 0)
                {
                    $this->buildParentWithChildrenRow($variation, $settings, $this->attributeName);
                    $this->buildChildRow($variation, $settings, $attributeValue);
                }
                elseif($variation['data']['variation']['isMain'] === true && strlen($attributeValue) == 0)
                {
                    $this->buildParentWithChildrenRow($variation, $settings, $this->attributeName);
                }
                else
                {
                    $this->buildChildRow($variation, $settings, $attributeValue);
                }

                $i++;
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

        $priceList = $this->getPriceList($item, $settings);

        $vat = $this->getVatClassId($priceList['vatValue']);

        $stockList = $this->getStockList($item);

        $basePriceComponentList = $this->getBasePriceComponentList($item);

        $data = [
            'id'						=> '',
            'variante_zu_id'			=> '',
            'artikelnummer'				=> $this->elasticExportHelper->generateSku($item['id'], self::RAKUTEN_DE, 0, $item['data']['skus']['sku']),
            'produkt_bestellbar'		=> $stockList['variationAvailable'],
            'produktname'				=> $this->elasticExportHelper->getName($item, $settings, 150),
            'hersteller'				=> $this->elasticExportHelper->getExternalManufacturerName($item['data']['item']['manufacturer']['id']),
            'beschreibung'				=> $this->elasticExportHelper->getDescription($item, $settings, 5000),
            'variante'					=> $this->attributeName[$item['data']['item']['id']],
            'variantenwert'				=> '',
            'isbn_ean'					=> $this->elasticExportHelper->getBarcodeByType($item, $settings->get('barcode')),
            'lagerbestand'				=> $stockList['stock'],
            'preis'						=> number_format((float)$priceList['price'], 2, '.', ''),
            'grundpreis_inhalt'			=> strlen($basePriceComponentList['unit']) ?
                number_format((float)$basePriceComponentList['content'],3,',','') : '',
            'grundpreis_einheit'		=> $basePriceComponentList['unit'],
            'reduzierter_preis'			=> $priceList['reducedPrice'] > 0 ?
                number_format((float)$priceList['reducedPrice'], 2, '.', '') : '',
            'bezug_reduzierter_preis'	=> $priceList['referenceReducedPrice'],
            'mwst_klasse'				=> $vat,
            'bestandsverwaltung_aktiv'	=> $stockList['inventoryManagementActive'],
            'bild1'						=> $this->getImageByNumber($item, 0),
            'bild2'						=> $this->getImageByNumber($item, 1),
            'bild3'						=> $this->getImageByNumber($item, 2),
            'bild4'						=> $this->getImageByNumber($item, 3),
            'bild5'						=> $this->getImageByNumber($item, 4),
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
            'bild6'						=> $this->getImageByNumber($item, 5),
            'bild7'						=> $this->getImageByNumber($item, 6),
            'bild8'						=> $this->getImageByNumber($item, 7),
            'bild9'						=> $this->getImageByNumber($item, 8),
            'bild10'					=> $this->getImageByNumber($item, 9),
            'technical_data'			=> $this->elasticExportHelper->getTechnicalData($item, $settings),
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
        $priceList = $this->getPriceList($item, $settings);

        $vat = $this->getVatClassId($priceList['vatValue']);

        $stockList = $this->getStockList($item);

        $data = [
            'id'						=> '#'.$item['data']['item']['id'],
            'variante_zu_id'			=> '',
            'artikelnummer'				=> '',
            'produkt_bestellbar'		=> '',
            'produktname'				=> $this->elasticExportHelper->getName($item, $settings, 150),
            'hersteller'				=> $this->elasticExportHelper->getExternalManufacturerName($item['data']['item']['manufacturer']['id']),
            'beschreibung'				=> $this->elasticExportHelper->getDescription($item, $settings, 5000),
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
            'bild1'						=> $this->getImageByNumber($item, 0),
            'bild2'						=> $this->getImageByNumber($item, 1),
            'bild3'						=> $this->getImageByNumber($item, 2),
            'bild4'						=> $this->getImageByNumber($item, 3),
            'bild5'						=> $this->getImageByNumber($item, 4),
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
            'bild6'						=> $this->getImageByNumber($item, 5),
            'bild7'						=> $this->getImageByNumber($item, 6),
            'bild8'						=> $this->getImageByNumber($item, 7),
            'bild9'						=> $this->getImageByNumber($item, 8),
            'bild10'					=> $this->getImageByNumber($item, 9),
            'technical_data'			=> $this->elasticExportHelper->getTechnicalData($item, $settings),
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

        $stockList = $this->getStockList($item);

        $priceList = $this->getPriceList($item, $settings);

        $basePriceComponentList = $this->getBasePriceComponentList($item);

        $data = [
            'id'						=> '',
            'variante_zu_id'			=> '#'.$item['data']['item']['id'],
            'artikelnummer'				=> $this->elasticExportHelper->generateSku($item['id'], self::RAKUTEN_DE, 0, $item['data']['skus']['sku']),
            'produkt_bestellbar'		=> $stockList['variationAvailable'],
            'produktname'				=> '',
            'hersteller'				=> '',
            'beschreibung'				=> '',
            'variante'					=> '',
            'variantenwert'				=> $attributeValue,
            'isbn_ean'					=> $this->elasticExportHelper->getBarcodeByType($item, $settings->get('barcode')),
            'lagerbestand'				=> $stockList['stock'],
            'preis'						=> number_format((float)$priceList['price'], 2, '.', ''),
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
     * @param array $item
     * @param int $number
     * @return string
     */
    private function getImageByNumber($item, int $number):string
    {
        if(is_array($item['data']['images']['all']) && count($item['data']['images']['all']) > 0)
        {
            return (string)$this->elasticExportHelper->getImageUrlBySize($item['data']['images']['all'][$number]);
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
        $stockRepository = pluginApp(StockRepositoryContract::class);
        if($stockRepository instanceof StockRepositoryContract)
        {
            $stockRepository->setFilters(['variationId' => $item['id']]);
        }
        $stockResult = $stockRepository->listStock(['stockNet'],1,1);
        $stockNet = $stockResult->getResult()->first()->stockNet;

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
                    $stock = 0;
                }
            }
        }

        return array (
            'stock'                     =>  $stock,
            'variationAvailable'        =>  $variationAvailable,
            'inventoryManagementActive' =>  $inventoryManagementActive,
        );

    }

    /**
     * Get a List of price, reduced price and the reference for the reduced price.
     * @param array $item
     * @param KeyValue $settings
     * @return array
     */
    private function getPriceList($item, KeyValue $settings):array
    {
        $variationPrice = 0.00;
        $vatValue = 19;

        //getting the retail price
        /**
         * SalesPriceSearchRequest $salesPriceSearchRequest
         */
        $salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
        if($salesPriceSearchRequest instanceof SalesPriceSearchRequest)
        {
            $salesPriceSearchRequest->variationId = $item['id'];
            $salesPriceSearchRequest->referrerId = $settings->get('referrerId');
        }
        /**
         * SalesPriceSearchRepository $salesPriceSearchRepository
         */
        $salesPriceSearchRepository = pluginApp(SalesPriceSearchRepository::class);
        if($salesPriceSearchRepository instanceof SalesPriceSearchRepository)
        {
            $salesPriceSearch  = $salesPriceSearchRepository->search($salesPriceSearchRequest);
            $variationPrice = $salesPriceSearch->price;
            $vatValue = $salesPriceSearch->vatValue;
        }

        //getting the recommended retail price
        if($settings->get('transferRrp') == self::TRANSFER_RRP_YES
            && $salesPriceSearchRepository instanceof SalesPriceSearchRepository)
        {
            $salesPriceSearchRequest->type = 'rrp';
            $variationRrp = $salesPriceSearchRepository->search($salesPriceSearchRequest)->price;
        }
        else
        {
            $variationRrp = 0.00;
        }

        //getting the special price
        if($settings->get('transferOfferPrice') == self::TRANSFER_OFFER_PRICE_YES
            && $salesPriceSearchRepository instanceof SalesPriceSearchRepository)
        {
            $salesPriceSearchRequest->type = 'specialOffer';
            $variationSpecialPrice = $salesPriceSearchRepository->search($salesPriceSearchRequest)->price;
        }
        else
        {
            $variationSpecialPrice = 0.00;
        }

        //setting retail price as selling price without a reduced price
        $price = $variationPrice;
        $reducedPrice = '';
        $referenceReducedPrice = '';

        if ($price != '' || $price != 0.00)
        {
            //if recommended retail price is set and higher than retail price...
            if ($variationRrp > 0 && $variationRrp > $variationPrice)
            {
                //set recommended retail price as selling price
                $price = $variationRrp;
                //set retail price as reduced price
                $reducedPrice = $variationPrice;
                //set recommended retail price as reference
                $referenceReducedPrice = 'UVP';
            }

            // if special offer price is set and lower than retail price and recommended retail price is already set as reference...
            if ($variationSpecialPrice > 0 && $variationPrice > $variationSpecialPrice && $referenceReducedPrice == 'UVP')
            {
                //set special offer price as reduced price
                $reducedPrice = $variationSpecialPrice;
            }
            //if recommended retail price is not set as reference then ...
            elseif ($variationSpecialPrice > 0 && $variationPrice > $variationSpecialPrice)
            {
                //set special offer price as reduced price and...
                $reducedPrice = $variationSpecialPrice;
                //set retail price as reference
                $referenceReducedPrice = 'VK';
            }
        }
        return array(
            'price'                     =>  $price,
            'reducedPrice'              =>  $reducedPrice,
            'referenceReducedPrice'     =>  $referenceReducedPrice,
            'vatValue'                  =>  $vatValue
        );
    }
}