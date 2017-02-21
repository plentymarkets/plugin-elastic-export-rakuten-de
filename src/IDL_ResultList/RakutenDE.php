<?php

namespace ElasticExportRakutenDE\IDL_ResultList;

use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Item\DataLayer\Models\RecordList;

class RakutenDE
{
    const RAKUTEN_DE = 106.00;
    /**
     * @param array $variationIds
     * @param KeyValue $settings
     * @param array $filter
     * @return RecordList|string
     */
    public function getResultList($variationIds, $settings, $filter = [])
    {
        $referrerId = $settings->get('referrerId') ? $settings->get('referrerId') : self::RAKUTEN_DE;

        if(is_array($variationIds) && count($variationIds) > 0)
        {
            $searchFilter = array(
                'variationBase.hasId' => array(
                    'id' => $variationIds
                )
            );
            if(array_key_exists('variationStock.netPositive' ,$filter))
            {
                $searchFilter['variationStock.netPositive'] = $filter['variationStock.netPositive'];
            }
            elseif(array_key_exists('variationStock.isSalable' ,$filter))
            {
                $searchFilter['variationStock.isSalable'] = $filter['variationStock.isSalable'];
            }

            $resultFields = array(
                'itemBase' => array(
                    'id',
                ),

                'variationBase' => array(
                    'id'
                ),

                'itemPropertyList' => array(
                    'params' => array(),
                    'fields' => array(
                        'itemPropertyId',
                        'propertyId',
                        'propertyValue',
                        'propertyValueType',
                    )
                ),

                'variationStock' => array(
                    'params' => array(
                        'type' => 'virtual'
                    ),
                    'fields' => array(
                        'stockNet'
                    )
                ),

                'variationRetailPrice' => array(
                    'params' => array(
                        'referrerId' => $referrerId,
                    ),
                    'fields' => array(
                        'price',
                        'vatValue',
                    ),
                ),

                'variationRecommendedRetailPrice' => array(
                    'params' => array(
                        'referrerId' => $referrerId,
                    ),
                    'fields' => array(
                        'price',    // uvp
                    ),
                ),

                'variationSpecialOfferRetailPrice' => array(
                    'params' => array(
                        'referrerId' => $referrerId,
                    ),
                    'fields' => array(
                        'retailPrice',
                    ),
                ),
            );

            $itemDataLayer = pluginApp(ItemDataLayerRepositoryContract::class);
            /**
             * @var ItemDataLayerRepositoryContract $itemDataLayer
             */
            $itemDataLayer = $itemDataLayer->search($resultFields, $searchFilter);
            return $itemDataLayer;
        }
        return '';
    }
}