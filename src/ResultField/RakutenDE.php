<?php

namespace ElasticExportRakutenDE\ResultField;

use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\DataExchange\Contracts\ResultFields;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Mutators\BarcodeMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\KeyMutator;
use Plenty\Modules\Item\Search\Mutators\SkuMutator;
use Plenty\Modules\Item\Search\Mutators\DefaultCategoryMutator;

/**
 * Class RakutenDE
 */
class RakutenDE extends ResultFields
{
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
		$this->setOrderByList(['item.id', ElasticSearch::SORTING_ORDER_ASC]);

		$accountId = (int) $settings->get('marketAccountId');
        $reference = $settings->get('referrerId') ? $settings->get('referrerId') : self::RAKUTEN_DE;

        // texts
        $itemDescriptionFields = ['texts.urlPath'];

        switch($settings->get('nameId'))
        {
            case 1:
                $itemDescriptionFields[] = 'texts.name1';
                break;
            case 2:
                $itemDescriptionFields[] = 'texts.name2';
                break;
            case 3:
                $itemDescriptionFields[] = 'texts.name3';
                break;
            default:
                $itemDescriptionFields[] = 'texts.name1';
                break;
        }

        if($settings->get('descriptionType') == 'itemShortDescription' || $settings->get('previewTextType') == 'itemShortDescription')
        {
            $itemDescriptionFields[] = 'texts.shortDescription';
        }

        if($settings->get('descriptionType') == 'itemDescription'
            || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData'
            || $settings->get('previewTextType') == 'itemDescription'
            || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData'
		)
        {
            $itemDescriptionFields[] = 'texts.description';
        }

        $itemDescriptionFields[] = 'texts.technicalData';
		$itemDescriptionFields[] = 'texts.lang';

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
        $languageMutator = pluginApp(LanguageMutator::class, [[$settings->get('lang')]]);

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

        $fields = [
            [
                //item
                'item.id',
                'item.manufacturer.id',
                'item.rakutenCategoryId',

                //variation
                'id',
                'variation.availability.id',
                'variation.stockLimitation',
                'variation.vatId',
                'variation.model',
                'variation.isMain',

                //images
                'images.all.urlMiddle',
                'images.all.urlPreview',
                'images.all.urlSecondPreview',
                'images.all.url',
                'images.all.path',
                'images.all.position',

                //unit
                'unit.content',
                'unit.id',

                //sku
                'skus.sku',
				'skus.parentSku',

                //defaultCategories
                'defaultCategories.id',

                //barcodes
                'barcodes.code',
                'barcodes.type',

                //attributes
                'attributes.attributeValueSetId',
                'attributes.attributeId',
                'attributes.valueId',
                'attributes.names.name',
                'attributes.names.lang',

                //properties
                'properties.property.id'
            ],

            [
                $languageMutator,
                $skuMutator,
                $defaultCategoryMutator,
				$barcodeMutator,
                $keyMutator
            ],
        ];

        if($reference != -1)
        {
            $fields[1][] = $imageMutator;
        }

        foreach($itemDescriptionFields as $itemDescriptionField)
        {
            $fields[0][] = $itemDescriptionField;
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