<?php

namespace ElasticExportRakutenDE\Validators;

use Plenty\Plugin\Log\Loggable;

class GeneratorValidator
{
    use Loggable;

    public function mainValidator($variation)
    {
        if(!array_key_exists('id', $variation))
        {
            $key = 'id';
            $path = 'item';
            $this->missingKeyLog($key, $path);

            return false;
        }

        if(!array_key_exists('item', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'item';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('variation', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'variation';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('images', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'images';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('unit', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'unit';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('skus', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'skus';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('defaultCategories', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'defaultCategories';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('barcodes', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'barcodes';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('attributes', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'attributes';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('properties', $variation['data']))
        {
            $path = 'item[\'data\']';
            $key = 'properties';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('stockLimitation', $variation['data']['variation']))
        {
            $path = 'item[\'data\'][\'variation\']';
            $key = 'stockLimitation';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('id', $variation['data']['item']))
        {
            $path = 'item[\'data\'][\'item\']';
            $key = 'id';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('isMain', $variation['data']['variation']))
        {
            $path = 'item[\'data\'][\'variation\']';
            $key = 'isMain';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('manufacturer', $variation['data']['item']))
        {
            $path = 'item[\'data\'][\'item\']';
            $key = 'manufacturer';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('id', $variation['data']['item']['manufacturer']))
        {
            $path = 'item[\'data\'][\'item\'][\'manufacturer\']';
            $key = 'id';
            $this->missingKeyLog($key, $path);
            return false;
        }

        if(!array_key_exists('rakutenCategoryId', $variation['data']['item']))
        {
            $path = 'item[\'data\'][\'item\']';
            $key = 'rakutenCategoryId';
            $this->missingKeyLog($key, $path);
            return false;
        }

        for($i = 1; $i <= 20; $i++)
        {
            if(!array_key_exists('free'.$i, $variation['data']['item']))
            {
                $path = 'item[\'data\'][\'item\']';
                $key = 'free'.$i;
                $this->missingKeyLog($key, $path);
                return false;
            }
        }

        if(!array_key_exists('rakutenCategoryId', $variation['data']['item']))
        {
            $path = 'item[\'data\'][\'item\']';
            $key = 'rakutenCategoryId';
            $this->missingKeyLog($key, $path);
            return false;
        }

        return true;
    }

    public function missingKeyLog($key, $path)
    {
        $this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.missingKey', [
            'Key' => $key,
            'Path' => $path
        ]);
    }
}