<?php

namespace ElasticExportRakutenDE;

use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Log\Services\ReferenceContainer;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;
use ElasticExportRakutenDE\Crons\ItemUpateCron as ItemUpateCron;
use Plenty\Plugin\ServiceProvider as ServiceProvider;

class ElasticExportRakutenDEServiceProvider extends ServiceProvider //DataExchangeServiceProvider
{
    public function register()
    {
        $this->getApplication()->singleton(GeneratorValidator::class);
    }

    public function boot(
    	ExportPresetContainer $exportPresetContainer,
		CronContainer $cronContainer,
		ReferenceContainer $referenceContainer)
	{
		$exportPresetContainer->add(
            'RakutenDE-Plugin',
            'ElasticExportRakutenDE\ResultField\RakutenDE',
            'ElasticExportRakutenDE\Generator\RakutenDE',
            '',
            true,
            true
        );

		$cronContainer->add(CronContainer::HOURLY, ItemUpateCron::class);
	}
}