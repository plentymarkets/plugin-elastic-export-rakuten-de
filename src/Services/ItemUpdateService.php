<?php

namespace ElasticExportRakutenDE\Services;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IndependentSource;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 *
 * @class ItemUpdateService
 */
class ItemUpdateService
{
	use Loggable;

	const RAKUTEN_DE = 106.00;

	/**
	 * ItemUpdateService constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Generates the content for updating stock and price of multiple items
	 * and variations.
	 *
	 * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
	 */
	public function generateContent(
		VariationElasticSearchScrollRepositoryContract $elasticSearch
	)
	{
		$this->prepareElasticSearchSearch($elasticSearch);

		$limitReached = false;

		if($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract)
		{
			do
			{
				if($limitReached === true)
				{
					break;
				}

				$resultList = $elasticSearch->execute();

				if(is_array($resultList['documents']) && count($resultList['documents']) > 0)
				{
					foreach($resultList['documents'] as $variation)
					{

					}
				}

			} while ($elasticSearch->hasNext());
		}
	}

	/**
	 * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
	 */
	private function prepareElasticSearchSearch($elasticSearch)
	{
		/**
		 * @var DocumentProcessor $documentProcessor
		 */
		$documentProcessor = pluginApp(DocumentProcessor::class);

		//Add each Mutator given from the resultColumns
		foreach($resultColumns[1] as $mutator)
		{
			$documentProcessor->addMutator($mutator);
		}


		/**
		 * @var IndependentSource $independentSource
		 */
		$independentSource = pluginApp(IndependentSource::class);

		//Add each Result Field from the resultColumns
		foreach($resultColumns[0] as $resultField)
		{
			$independentSource->activate($resultField);
		}

		/**
		 * @var DocumentSearch $documentSearch
		 */
		$documentSearch = pluginApp(DocumentSearch::class, [$documentProcessor]);
		$documentSearch->addSource($independentSource);


		/**
		 * @var SingleSorting $singleSorting
		 */
		$singleSorting = pluginApp(SingleSorting::class, ['variation.itemId', 'ASC']);
		$documentSearch->setSorting($singleSorting);



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
			'item.id',
			'skus.sku'
		];
	}
}