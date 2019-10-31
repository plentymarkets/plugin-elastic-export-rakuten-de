<?php

namespace ElasticExportRakutenDE\Helper;

use ElasticExport\Services\PriceDetectionService;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPrice;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Order\Currency\Contracts\CurrencyConversionSettingsRepositoryContract;

/**
 * Class ItemUpdatePriceHelper
 *
 * @package ElasticExportRakutenDE\Helper
 */
class PriceHelper
{
    const TRANSFER_RRP_YES = 1;
    const TRANSFER_OFFER_PRICE_YES = 1;

    /**
     * @var SalesPriceRepositoryContract
     */
    private $salesPriceRepository;

    /**
     * @var array
     */
    private $salesPriceCurrencyList = [];

    /**
     * @var array
     */
    private $currencyConversionList = [];

    /**
     * @var PriceDetectionService
     */
    private $priceDetectionService = null;

    /**
     * @var int
     */
    private $countryId = null;

    /**
     * @var bool
     */
    private $preload = true;

    /**
     * ItemUpdatePriceHelper constructor.
     *
     * @param SalesPriceRepositoryContract $salesPriceRepository
     * @param CurrencyConversionSettingsRepositoryContract $currencyConversionSettingsRepositoryContract
     */
    public function __construct(
        SalesPriceRepositoryContract $salesPriceRepository,
        CurrencyConversionSettingsRepositoryContract $currencyConversionSettingsRepositoryContract)
    {
        $this->salesPriceRepository = $salesPriceRepository;

        $this->currencyConversionList = $currencyConversionSettingsRepositoryContract->getCurrencyConversionList();
    }

    /**
     * Preloads some data for the price calculation.
     *
     * @param KeyValue $settings
     */
    private function preload(KeyValue $settings)
    {
        $this->countryId = $settings->get('destination');

        $this->priceDetectionService = pluginApp(PriceDetectionService::class);
        $this->priceDetectionService->preload($settings);
    }

    /**
     * Resets loaded and cached information for the calculation of the prices.
     */
    public function reset()
    {
        $this->preload = true;
    }

    /**
     * Get a List of price, reduced price and the reference for the reduced price.
     *
     * @param KeyValue $settings
     * @param array $preloadedPrices
     * @return array
     */
    public function getPriceData(KeyValue $settings, array $preloadedPrices):array
    {
        $rrpUpdatedTimestamp = '';
        $specialPriceUpdatedTimestamp = '';
        
        if ($this->preload == true) {
            $this->preload($settings);
            $this->preload = false;
        }

        // Get the retail price
        $retailPrice = $this->priceDetectionService->getPriceByPreloadList($preloadedPrices);
        $variationPrice = $this->getPriceByRetailPriceSettings($retailPrice, $settings);
        $variationPriceUpdatedTimestamp = $retailPrice['updatedAt'];

        // Get the recommended retail price
        if ($settings->get('transferRrp') == self::TRANSFER_RRP_YES) {
            $recommendedRetailPrice = $this->priceDetectionService->getPriceByPreloadList($preloadedPrices, PriceDetectionService::RRP);
            $variationRrp = $this->getPriceByRetailPriceSettings($recommendedRetailPrice, $settings);
            $rrpUpdatedTimestamp = $recommendedRetailPrice['updatedAt'];
        } else {
            $variationRrp = 0.00;
        }

        // Get the special price
        if ($settings->get('transferOfferPrice') == self::TRANSFER_OFFER_PRICE_YES) {
            $specialPrice = $this->priceDetectionService->getPriceByPreloadList($preloadedPrices, PriceDetectionService::SPECIAL_PRICE);
            $variationSpecialPrice = $this->getPriceByRetailPriceSettings($specialPrice, $settings);
            $specialPriceUpdatedTimestamp = isset($specialPrice['updatedAt']) ? $specialPrice['updatedAt'] : 0;
        } else {
            $variationSpecialPrice = 0.00;
        }

        // setting retail price as selling price without a reduced price
        $price = $variationPrice;
        $reducedPrice = '';
        $referenceReducedPrice = '';
        $reducedPriceUpdatedTimestamp = '';

        if ($price != '' || $price != 0.00) {
            //if recommended retail price is set and higher than retail price...
            if ($variationRrp > 0 && $variationRrp > $variationPrice) {
                //set recommended retail price as selling price
                $price = $variationRrp;
                //set retail price as reduced price
                $reducedPrice = $variationPrice;
                //set recommended retail price as reference
                $referenceReducedPrice = 'UVP';

                $reducedPriceUpdatedTimestamp = $variationPriceUpdatedTimestamp;
                $variationPriceUpdatedTimestamp = $rrpUpdatedTimestamp;
            }

            // if special offer price is set and lower than retail price and recommended retail price is already set as reference...
            if ($variationSpecialPrice > 0 && $variationPrice > $variationSpecialPrice && $referenceReducedPrice == 'UVP') {
                //set special offer price as reduced price
                $reducedPrice = $variationSpecialPrice;
                $reducedPriceUpdatedTimestamp = $specialPriceUpdatedTimestamp;
            //if recommended retail price is not set as reference then ...
            } elseif ($variationSpecialPrice > 0 && $variationPrice > $variationSpecialPrice) {
                //set special offer price as reduced price and...
                $reducedPrice = $variationSpecialPrice;
                $reducedPriceUpdatedTimestamp = $specialPriceUpdatedTimestamp;
                //set retail price as reference
                $referenceReducedPrice = 'VK';
            }
        }

        return [
            'price' => $price,
            'reducedPrice' => $reducedPrice,
            'referenceReducedPrice' => $referenceReducedPrice,
            'variationPriceUpdatedTimestamp' => $variationPriceUpdatedTimestamp,
            'reducedPriceUpdatedTimestamp' => $reducedPriceUpdatedTimestamp
        ];
    }

    /**
     * Gets the price by the format setting for the retail price.
     *
     * @param array $priceData
     * @param KeyValue $settings
     * @return string
     */
    private function getPriceByRetailPriceSettings(array $priceData, KeyValue $settings)
    {
        if (isset($priceData['price'])) {
            $price = $this->calculatePriceByCurrency($priceData, $priceData['price'], $settings);
            return $price;
        }

        return '';
    }

    /**
     * Gets the calculated price for a given currency.
     *
     * @param array $priceData
     * @param $price
     * @param KeyValue $settings
     * @return mixed
     */
    private function calculatePriceByCurrency(array $priceData, $price, KeyValue $settings)
    {
        if ($settings->get('liveConversion', false) == true && count($this->currencyConversionList) > 0 && $price > 0) {
            if (array_key_exists($priceData['salesPriceId'],
                    $this->salesPriceCurrencyList) && $this->salesPriceCurrencyList[$priceData['salesPriceId']] === true) 
            {
                $price = $price * $this->currencyConversionList['list']['EUR']['exchange_ratio'];
                return $price;
            } elseif (array_key_exists($priceData['salesPriceId'], $this->salesPriceCurrencyList) &&
                      $this->salesPriceCurrencyList[$priceData['salesPriceId']] === false)
            {
                return $price;
            }

            $salesPriceData = $this->salesPriceRepository->findById($priceData['salesPriceId']);

            if ($salesPriceData instanceof SalesPrice) {
                $salePriceCurrencyData = $salesPriceData->currencies->whereIn('currency', [$this->currencyConversionList['default'], "-1"]);

                if (count($salePriceCurrencyData)) {
                    $this->salesPriceCurrencyList[$priceData['salesPriceId']] = true;

                    $price = $price * $this->currencyConversionList['list']['EUR']['exchange_ratio'];

                    return $price;
                } else {
                    $this->salesPriceCurrencyList[$priceData['salesPriceId']] = false;
                }
            }
        }

        return $price;
    }
}