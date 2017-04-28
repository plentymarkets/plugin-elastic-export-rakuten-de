<?php

namespace ElasticExportRakutenDE\DataProvider;

use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Search\Filter\SkuFilter;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IndependentSource;
use Plenty\Modules\Market\Credentials\Models\Credentials;

class ElasticSearchDataProvider
{
	const RAKUTEN_DE = 106.00;

	/**
	 * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
	 * @param Credentials $rakutenCredential
	 * @return VariationElasticSearchScrollRepositoryContract $elasticSearch
	 */
	public function prepareElasticSearchSearch($elasticSearch, $rakutenCredential)
	{
		$resultFields = $this->getResultFields();
		/**
		 * @var IndependentSource $independentSource
		 */
		$independentSource = pluginApp(IndependentSource::class);

		if($independentSource instanceof IndependentSource)
 		{
			//Add each Result Field from the resultColumns
			$independentSource->activateList($resultFields);
		}

		/**
		 * @var DocumentSearch $documentSearch
		 */
		$documentSearch = pluginApp(DocumentSearch::class);
		if($documentSearch instanceof DocumentSearch)
		{
			$documentSearch->addSource($independentSource);
		}

		$skuFilter = pluginApp(SkuFilter::class);
		if($skuFilter instanceof SkuFilter)
		{
			$skuFilter->hasMarketId(self::RAKUTEN_DE);

			$accountId = 0;
			if($rakutenCredential instanceof Credentials)
			{
				$accountId = $rakutenCredential->data['id'];
			}

			$skuFilter->hasAccountId($accountId);

			$documentSearch->addFilter($skuFilter);
		}

		/**
		 * @var SortingInterface $sortingInterface
		 */
		$sortingInterface = pluginApp(SortingInterface::class, ['variation.itemId', 'ASC']);
		if($sortingInterface instanceof SortingInterface)
		$documentSearch->setSorting($sortingInterface);

		$elasticSearch->addSearch($documentSearch);

		return $elasticSearch;
	}

	/**
	 * Returns specific result fields for the elastic search search
	 * which are needed for the item update.
	 *
	 * @return array
	 */
	private function getResultFields()
	{
		return [
			'id',

			//item
			'item.id',

			//variation
			'variation.isMain',
			'variation.stockLimitation',

			//skus
			'skus.sku',

			//attributes
			'attributes.attributeValueSetId',
			'attributes.attributeId',
			'attributes.valueId',
			'attributes.names.name',
			'attributes.names.lang',
		];
	}
}