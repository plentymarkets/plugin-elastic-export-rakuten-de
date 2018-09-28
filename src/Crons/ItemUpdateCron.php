<?php

namespace ElasticExportRakutenDE\Crons;

use ElasticExportRakutenDE\Services\ItemUpdateService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * @class ItemUpdateCron
 */
class ItemUpdateCron
{
    use Loggable;

    /**
     * ItemUpdateCron constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param ItemUpdateService $itemUpdateService
     */
    public function handle(ItemUpdateService $itemUpdateService)
    {
        try
        {
            $configRepository = pluginApp(ConfigRepository::class);

            $priceUpdate = $configRepository->get('ElasticExportRakutenDE.update_settings.price_update');
            $stockUpdate = $configRepository->get('ElasticExportRakutenDE.update_settings.stock_update');

            if($priceUpdate == 'true' || $stockUpdate == 'true') {
                $itemUpdateService->stockUpdate = $stockUpdate;
                $itemUpdateService->priceUpdate = $priceUpdate;

                $itemUpdateService->generateContent();
            }
        }
        catch(\Throwable $throwable)
        {
            $this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.cronError', [
                'error' => $throwable->getMessage(),
                'line' => $throwable->getLine(),
            ]);
        }
    }
}