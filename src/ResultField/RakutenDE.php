<?php

namespace ElasticExportRakutenDE\ResultField;

use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\DataExchange\Contracts\ResultFields;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Mutators\BarcodeMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\KeyMutator;
use Plenty\Modules\Item\Search\Mutators\SkuMutator;
use Plenty\Modules\Item\Search\Mutators\DefaultCategoryMutator;
use ElasticExport\DataProvider\ResultFieldDataProvider;
use Plenty\Plugin\Log\Loggable;

/**
 * Class RakutenDE
 */
class RakutenDE extends ResultFields
{
	use Loggable;

    const RAKUTEN_DE = 106.00;
    /*
     * @var ArrayHelper
     */
    private $arrayHelper;

    /**
     * Rakuten constructor.
	 *
     * @param ArrayHelper $arrayHelper
     */
    public function __construct(ArrayHelper $arrayHelper)
    {
        $this->arrayHelper = $arrayHelper;
    }

    /**
     * Generate result fields.
	 *
     * @param  array $formatSettings = []
     * @return array
     */
    public function generateResultFields(array $formatSettings = []):array
    {
        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');
		$this->setOrderByList([
			'path' => 'item.id',
			'order' => ElasticSearch::SORTING_ORDER_ASC]);

		$accountId = (int) $settings->get('marketAccountId');
        $reference = $settings->get('referrerId') ? $settings->get('referrerId') : self::RAKUTEN_DE;

        //Mutator

		/**
         * @var KeyMutator $keyMutator
         */
        $keyMutator = pluginApp(KeyMutator::class);

        if($keyMutator instanceof KeyMutator)
        {
            $keyMutator->setKeyList($this->getKeyList());
            $keyMutator->setNestedKeyList($this->getNestedKeyList());
        }

		/**
		 * @var BarcodeMutator $barcodeMutator
		 */
		$barcodeMutator = pluginApp(BarcodeMutator::class);
		if($barcodeMutator instanceof BarcodeMutator)
		{
			$barcodeMutator->addMarket($reference);
		}

        /**
         * @var ImageMutator $imageMutator
         */
        $imageMutator = pluginApp(ImageMutator::class);

        if($imageMutator instanceof ImageMutator)
        {
            $imageMutator->addMarket($reference);
        }

        /**
         * @var LanguageMutator $languageMutator
         */
		$languageMutator = pluginApp(LanguageMutator::class, ['languages' => [$settings->get('lang')]]);

        /**
         * @var SkuMutator $skuMutator
         */
        $skuMutator = pluginApp(SkuMutator::class);

        if($skuMutator instanceof SkuMutator)
        {
        	$skuMutator->setAccount($accountId);
            $skuMutator->setMarket($reference);
        }

        /**
         * @var DefaultCategoryMutator $defaultCategoryMutator
         */
        $defaultCategoryMutator = pluginApp(DefaultCategoryMutator::class);

        if($defaultCategoryMutator instanceof DefaultCategoryMutator)
        {
            $defaultCategoryMutator->setPlentyId($settings->get('plentyId'));
        }

		$resultFieldHelper = pluginApp(ResultFieldDataProvider::class);
		if($resultFieldHelper instanceof ResultFieldDataProvider)
		{
			$resultFields = $resultFieldHelper->getResultFields($settings);
		}

		if(isset($resultFields) && is_array($resultFields) && count($resultFields))
		{
			$fields[0] = $resultFields;
			$fields[1] = [
				$languageMutator,
				$skuMutator,
				$defaultCategoryMutator,
				$barcodeMutator,
				$keyMutator
			];

			if($reference != -1)
			{
				$fields[1][] = $imageMutator;
			}
		}
		else
		{
			$this->getLogger(__METHOD__)->critical('ElasticExportRakutenDE::log.resultFieldError');
			exit();
		}

        return $fields;
    }

	/**
	 * Returns the key list for Elastic Export.
	 *
	 * @return array
	 */
    private function getKeyList()
    {
        $keyList = [
            //item
            'item.id',
            'item.manufacturer.id',
            'item.rakutenCategoryId',

            //variation
            'variation.availability.id',
            'variation.stockLimitation',
            'variation.vatId',
            'variation.model',
            'variation.isMain',

            //unit
            'unit.content',
            'unit.id',
        ];

        return $keyList;
    }

	/**
	 * Returns the nested key list for Elastic Export.
	 *
	 * @return mixed
	 */
    private function getNestedKeyList()
    {
        $nestedKeyList['keys'] = [
            //images
            'images.all',

            //sku
            'skus',

            //texts
            'texts',

            //defaultCategories
            'defaultCategories',

            //barcodes
            'barcodes',

            //attributes
            'attributes',

            //properties
            'properties'
        ];

        $nestedKeyList['nestedKeys'] = [
            'images.all' => [
                'urlMiddle',
                'urlPreview',
                'urlSecondPreview',
                'url',
                'path',
                'position',
            ],

            'skus' => [
                'sku',
				'parentSku'
            ],

            'texts'  => [
                'urlPath',
                'name1',
                'name2',
                'name3',
                'shortDescription',
                'description',
                'technicalData',
            ],

            'defaultCategories' => [
                'id'
            ],

            'barcodes'  => [
                'code',
                'type',
            ],

            'attributes'   => [
                'attributeValueSetId',
                'attributeId',
                'valueId',
                'names.name',
                'names.lang',
            ],

            'properties'    => [
                'property.id',
            ]
        ];

        return $nestedKeyList;
    }
}
