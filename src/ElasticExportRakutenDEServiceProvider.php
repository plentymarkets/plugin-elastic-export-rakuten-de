<?php

namespace ElasticExportRakutenDE;

use ElasticExportRakutenDE\Helper\SkuHelper;
use ElasticExportRakutenDE\Validators\GeneratorValidator;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use ElasticExportRakutenDE\Crons\ItemUpdateCron;
use Plenty\Plugin\ServiceProvider as ServiceProvider;

/**
 * Class ElasticExportRakutenDEServiceProvider
 *
 * @package ElasticExportRakutenDE
 */
class ElasticExportRakutenDEServiceProvider extends ServiceProvider
{
    const ORDER_REFERRER_RAKUTEN_DE = 106.00;
    
    public function register()
    {
        $this->getApplication()->singleton(GeneratorValidator::class);
        $this->getApplication()->singleton(SkuHelper::class);
    }

	/**
	 * @param ExportPresetContainer $exportPresetContainer
	 * @param CronContainer $cronContainer
	 */
    public function boot(ExportPresetContainer $exportPresetContainer, CronContainer $cronContainer)
	{

		// Adds the export format to the export container.
		$exportPresetContainer->add(
            'RakutenDE-Plugin',
            'ElasticExportRakutenDE\ResultField\RakutenDE',
            'ElasticExportRakutenDE\Generator\RakutenDE',
            '',
            true,
            true
        );

		// Adds crons to the cron list.
		$cronContainer->add(CronContainer::HOURLY, ItemUpdateCron::class);
	}
}