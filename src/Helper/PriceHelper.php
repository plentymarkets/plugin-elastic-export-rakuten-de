<?php

namespace ElasticExportRakutenDE\Helper;

use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPrice;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Order\Currency\Contracts\CurrencyRepositoryContract;

class PriceHelper
{
	const TRANSFER_RRP_YES = 1;
	const TRANSFER_OFFER_PRICE_YES = 1;
	const NET_PRICE = 'netPrice';
	const GROSS_PRICE = 'grossPrice';

	const RAKUTEN_DE = 106.00;
	/**
	 * @var SalesPriceSearchRepositoryContract
	 */
	private $salesPriceSearchRepositoryContract;
	/**
	 * @var CurrencyRepositoryContract
	 */
	private $currencyRepositoryContract;

	/**
	 * PriceHelper constructor.
	 * @param SalesPriceSearchRepositoryContract $salesPriceSearchRepositoryContract
	 * @param CurrencyRepositoryContract $currencyRepositoryContract
	 */
	public function __construct(
		SalesPriceSearchRepositoryContract $salesPriceSearchRepositoryContract,
		CurrencyRepositoryContract $currencyRepositoryContract)
	{
		$this->salesPriceSearchRepositoryContract = $salesPriceSearchRepositoryContract;
		$this->currencyRepositoryContract = $currencyRepositoryContract;
	}

	/**
	 * Get a List of price, reduced price and the reference for the reduced price.
	 * @param array $variation
	 * @param KeyValue $settings
	 * @return SalesPriceSearchResponse
	 */
	public function getPrice($variation, $settings)
	{
		//getting the retail price
		/**
		 * SalesPriceSearchRequest $salesPriceSearchRequest
		 */
		$salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
		if($salesPriceSearchRequest instanceof SalesPriceSearchRequest)
		{
			$countryId = $settings->get('destination');
			$currency = $this->currencyRepositoryContract->getCountryCurrency($countryId)->currency;

			$salesPriceSearchRequest->variationId = $variation['id'];
			$salesPriceSearchRequest->referrerId = $settings->get('referrerId');
			$salesPriceSearchRequest->plentyId = $settings->get('plentyId');
			$salesPriceSearchRequest->type = 'default';
			$salesPriceSearchRequest->countryId = $countryId;
			$salesPriceSearchRequest->currency = $currency;
		}

		$salesPriceSearch  = $this->salesPriceSearchRepositoryContract->search($salesPriceSearchRequest);

		return $salesPriceSearch;
	}

	/**
	 * Get a List of price, reduced price and the reference for the reduced price.
	 * @param array $item
	 * @param KeyValue $settings
	 * @return array
	 */
	public function getPriceList($item, KeyValue $settings):array
	{
		$variationPrice = '';
		$countryId = $settings->get('destination');
		$currency = $this->currencyRepositoryContract->getCountryCurrency($countryId)->currency;

		//getting the retail price
		/**
		 * SalesPriceSearchRequest $salesPriceSearchRequest
		 */
		$salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
		if($salesPriceSearchRequest instanceof SalesPriceSearchRequest)
		{
			$salesPriceSearchRequest->variationId = $item['id'];
			$salesPriceSearchRequest->referrerId = $settings->get('referrerId');
			$salesPriceSearchRequest->plentyId = $settings->get('plentyId');
			$salesPriceSearchRequest->type = 'default';
			$salesPriceSearchRequest->countryId = $countryId;
			$salesPriceSearchRequest->currency = $currency;
		}

		$salesPriceSearch  = $this->salesPriceSearchRepositoryContract->search($salesPriceSearchRequest);

		if(isset($salesPriceSearch->price) &&
		   ($settings->get('retailPrice') == self::GROSS_PRICE || is_null($settings->get('retailPrice'))))
		{
			$variationPrice = $salesPriceSearch->price;
		}
		elseif(isset($salesPriceSearch->priceNet) && $settings->get('retailPrice') == self::NET_PRICE)
		{
			$variationPrice = $salesPriceSearch->priceNet;
		}
		
		$vatValue = $salesPriceSearch->vatValue;

		//getting the recommended retail price
		if($settings->get('transferRrp') == self::TRANSFER_RRP_YES)
		{
			$variationRrp = '';
			$salesPriceSearchRequest->type = 'rrp';
			
			$salesPriceSearch = $this->salesPriceSearchRepositoryContract->search($salesPriceSearchRequest);

			if(isset($salesPriceSearch->price) &&
				($settings->get('retailPrice') == self::GROSS_PRICE || is_null($settings->get('retailPrice'))))
			{
				$variationRrp = $salesPriceSearch->price;
			}
			elseif(isset($salesPriceSearch->priceNet) && $settings->get('retailPrice') == self::NET_PRICE)
			{
				$variationRrp = $salesPriceSearch->priceNet;
			}
		}
		else
		{
			$variationRrp = 0.00;
		}

		//getting the special price
		if($settings->get('transferOfferPrice') == self::TRANSFER_OFFER_PRICE_YES)
		{
			$variationSpecialPrice = '';
			$salesPriceSearchRequest->type = 'specialOffer';
			$salesPriceSearch = $this->salesPriceSearchRepositoryContract->search($salesPriceSearchRequest);

			if(isset($salesPriceSearch->price) &&
				($settings->get('retailPrice') == self::GROSS_PRICE || is_null($settings->get('retailPrice'))))
			{
				$variationSpecialPrice = $salesPriceSearch->price;
			}
			elseif(isset($salesPriceSearch->priceNet) && $settings->get('retailPrice') == self::NET_PRICE)
			{
				$variationSpecialPrice = $salesPriceSearch->priceNet;
			}
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