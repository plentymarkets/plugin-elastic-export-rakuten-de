<?php

namespace ElasticExportRakutenDE\DataProvider;

use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\MutatorInterface;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Modules\Item\Search\Filter\SkuFilter;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IndependentSource;
use Plenty\Modules\Item\Search\Mutators\KeyMutator;
use Plenty\Modules\Item\Search\Mutators\SkuMutator;
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

		//ResultList
		/**
		 * @var IndependentSource $independentSource
		 */
		$independentSource = pluginApp(IndependentSource::class);

		if($independentSource instanceof IndependentSource)
 		{
			//Add each Result Field from the resultColumns
			$independentSource->activateList($resultFields);
		}

		$accountId = 0;
		if($rakutenCredential instanceof Credentials)
		{
			$accountId = $rakutenCredential->id;
		}

		/**
		 * @var DocumentProcessor $documentProcessor
		 */
		$documentProcessor = pluginApp(DocumentProcessor::class);

		/**
		 * @var SkuMutator $skuMutator
		 */
		$skuMutator = pluginApp(SkuMutator::class);

		if($skuMutator instanceof SkuMutator)
		{
			$skuMutator->setAccount($accountId);
			$skuMutator->setMarket((int)self::RAKUTEN_DE);

			if($documentProcessor instanceof DocumentProcessor)
			{
				if($skuMutator instanceof MutatorInterface)
				{
					$documentProcessor->addMutator($skuMutator);
				}
			}
		}

		$keyMutator = pluginApp(KeyMutator::class);

		if($keyMutator instanceof KeyMutator)
		{
			$keyMutator->setKeyList($this->getKeyList());
			$keyMutator->setNestedKeyList($this->getNestedKeyList());

			if($documentProcessor instanceof DocumentProcessor)
			{
				if($keyMutator instanceof MutatorInterface)
				{
					$documentProcessor->addMutator($keyMutator);
				}
			}
		}

		/**
		 * @var DocumentSearch $documentSearch
		 */
		$documentSearch = pluginApp(DocumentSearch::class, [$documentProcessor]);
		if($documentSearch instanceof DocumentSearch)
		{
			$documentSearch->addSource($independentSource);
		}

		$skuFilter = pluginApp(SkuFilter::class);
		if($skuFilter instanceof SkuFilter)
		{
			$skuFilter->hasMarketId(self::RAKUTEN_DE);

			$skuFilter->hasAccountId($accountId);

			$documentSearch->addFilter($skuFilter);
		}

		$singleSorting = pluginApp(SingleSorting::class, ['item.id', ElasticSearch::SORTING_ORDER_ASC]);
		if($singleSorting instanceof SingleSorting)
		{
			$documentSearch->setSorting($singleSorting);
		}

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
			//item
			'item.id',

			//variation
			'variation.isMain',
			'variation.stockLimitation',
			'variation.isActive',

			//skus
			'skus.sku',

			//attributes
			'attributes.attributeValueSetId',
			'attributes.attributeId',
			'attributes.valueId',
			'attributes.names.name',
			'attributes.names.lang',

			//ids
			'ids.markets',
		];
	}

	private function getKeyList()
	{
		$keyList = [
			//item
			'item.id',

			//variation
			'variation.stockLimitation',
			'variation.isMain',
		];

		return $keyList;
	}

	private function getNestedKeyList()
	{
		$nestedKeyList['keys'] = [
			//sku
			'skus',

			//attributes
			'attributes',

			//ids
			'ids.market'
		];

		$nestedKeyList['nestedKeys'] = [

			'skus' => [
				'sku'
			],

			'attributes'   => [
				'attributeValueSetId',
				'attributeId',
				'valueId',
				'names.name',
				'names.lang',
			],
		];

		return $nestedKeyList;
	}
}