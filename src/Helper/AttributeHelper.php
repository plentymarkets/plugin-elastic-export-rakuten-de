<?php


namespace ElasticExportRakutenDE\Helper;

use ElasticExport\Helper\ElasticExportCoreHelper;
use Plenty\Modules\Helper\Models\KeyValue;

class AttributeHelper
{
    /**
     * @var ElasticExportCoreHelper $elasticExportCoreHelper
     */
    private $elasticExportCoreHelper;
    
    /**
     * @var array
     */
    private $attributeNames = [];

    /**
     * @var array
     */
    private $attributeNameCombination = [];

    /**
     * PriceHelper constructor.
     * @param ElasticExportCoreHelper $elasticExportCoreHelper
     */
    public function __construct(ElasticExportCoreHelper $elasticExportCoreHelper)
    {
        $this->elasticExportCoreHelper = $elasticExportCoreHelper;
    }
    
    public function getAttributeNames ()
    {
        return $this->attributeNames;
    }

    public function getAttributeNameCombination ()
    {
        return $this->attributeNameCombination;
    }
    
    /**
     * @param array $variations
     * @param KeyValue $settings
     * @return array
     */
    public function getPreparedVariantItem(array $variations, KeyValue $settings):array
    {
        $attributesBuilt = false;

        foreach($variations as $key => $variation) {
            if ($attributesBuilt === false) {
                $attributesBuilt = $this->buildRakutenAttributeData($variation, $settings);
            } else {
                break;
            }
        }

        // remove main variations without attributes because they are not to be exported
        if ($attributesBuilt === true) {
            $variations = $this->removeMainVariationsWithoutAttributes($variations);
        }

        return $variations;
    }
    
    /**
     * Build the rakuten attribute name and the attribute order by the first variation with attributes
     * Return true, if build was successful
     *
     * @param array $variation
     * @param KeyValue $settings
     * @return bool
     */
    private function buildRakutenAttributeData(array $variation, KeyValue $settings):bool
    {
        $itemId = $variation['data']['item']['id'];
        if (!isset($this->attributeNameCombination[$itemId])) {
            foreach ($variation['data']['attributes'] as $attribute) {
                if (isset($attribute['attributeId']) && isset($attribute['attributeValueSetId'])) {
                    $this->attributeNameCombination[$itemId][] = $attribute['attributeId'];
                    if (!isset($this->attributeNames[$itemId])) {
                        $this->attributeNames[$itemId] = $this->elasticExportCoreHelper->getAttributeName($variation, $settings);
                    }
                }
            }
        }

        return isset($this->attributeNames[$itemId]) && isset($this->attributeNameCombination[$itemId]);
    }

    /**
     * search for main variation and remove it if it has no attribute
     *
     * This function should only be used on items after it was ensured that it has variations with attributes
     *
     * @param array $variations
     * @return array
     */
    private function removeMainVariationsWithoutAttributes(array $variations):array
    {
        foreach ($variations as $key => $variation) {
            if (isset($variation['data']['variation']['isMain']) &&
                $variation['data']['variation']['isMain'] === true)
            {
                if (!isset($variation['data']['attributes'][0]['attributeId'])) {
                    unset($variations[$key]);
                    $variations = array_values($variations);
                }
                break;
            }
        }
        return $variations;
    }

    /**
     * @param array $variation
     * @param KeyValue $settings
     * @return string
     */
    public function getRakutenAttributeValueString(array $variation, KeyValue $settings):string 
    {
        $attributeNameCombination = $this->attributeNameCombination[$variation['data']['item']['id']] ?? null;
        
        return $this->elasticExportCoreHelper->getAttributeValueSetShortFrontendName(
            $variation, $settings, '|', $attributeNameCombination
        );
    }

    public function resetAttributeCache()
    {
        $this->attributeNames = [];
        $this->attributeNameCombination = [];
    }
    
}