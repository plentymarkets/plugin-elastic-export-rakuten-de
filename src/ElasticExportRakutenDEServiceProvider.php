<?php

namespace ElasticExportRakutenDE;

use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use ElasticExportRakutenDE\Crons\ItemUpdateCron;
use Plenty\Plugin\ServiceProvider as ServiceProvider;

class ElasticExportRakutenDEServiceProvider extends ServiceProvider //DataExchangeServiceProvider
{

    public function register()
    {
        $this->getApplication()->singleton(GeneratorValidator::class);
    }

	/**
	 * @param ExportPresetContainer $exportPresetContainer
	 * @param CronContainer $cronContainer
	 */
    public function boot(
    	ExportPresetContainer $exportPresetContainer,
		CronContainer $cronContainer)
	{

		//Adds the export format to the export container.
		$exportPresetContainer->add(
            'RakutenDE-Plugin',
            'ElasticExportRakutenDE\ResultField\RakutenDE',
            'ElasticExportRakutenDE\Generator\RakutenDE',
            '',
            true,
            true
        );

		//Adds crons to the cron list.
		$cronContainer->add(CronContainer::HOURLY, ItemUpdateCron::class);
	}
}