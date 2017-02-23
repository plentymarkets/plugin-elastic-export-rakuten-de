<?php

namespace ElasticExportRakutenDE;

use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;

class ElasticExportRakutenDEServiceProvider extends DataExchangeServiceProvider
{
    public function register()
    {

    }

    public function exports(ExportPresetContainer $container)
    {
        $container->add(
            'RakutenDE-Plugin',
            'ElasticExportRakutenDE\ResultField\RakutenDE',
            'ElasticExportRakutenDE\Generator\RakutenDE',
            true
        );
    }
}