<?php

namespace ElasticExportRakutenDE;

use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;

class ElasticExportRakutenDEServiceProvider extends DataExchangeServiceProvider
{
    public function register()
    {
        $this->getApplication()->singleton(GeneratorValidator::class);
    }

    public function exports(ExportPresetContainer $container)
    {
        $container->add(
            'RakutenDE-Plugin',
            'ElasticExportRakutenDE\ResultField\RakutenDE',
            'ElasticExportRakutenDE\Generator\RakutenDE',
            '',
            true,
            true
        );
    }
}