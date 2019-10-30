<?php

namespace ElasticExportRakutenDE\Crons;

use ElasticExportRakutenDE\Services\ItemUpdateService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;
use ElasticExportRakutenDE\Exceptions\EmptyResponseException;

/**
 * Class RakutenItemUpdateCron
 *
 * @package ElasticExportRakutenDE\Crons
 */
class RakutenItemUpdateCron
{
    use Loggable;

    /**
     * @param ItemUpdateService $itemUpdateService
     */
    public function handle(ItemUpdateService $itemUpdateService)
    {
        try {
            $configRepository = pluginApp(ConfigRepository::class);

            $itemUpdateService->exportPrice = (bool)filter_var(
                $configRepository->get('ElasticExportRakutenDE.update_settings.price_update'), 
                FILTER_VALIDATE_BOOLEAN
            );
            $itemUpdateService->exportStock =  (bool)filter_var(
                $configRepository->get('ElasticExportRakutenDE.update_settings.stock_update'),
                FILTER_VALIDATE_BOOLEAN
            );

            if ($itemUpdateService->exportStock || $itemUpdateService->exportPrice) {
                $itemUpdateService->export();
            }
        }
        catch (EmptyResponseException $exception) {
            $this->getLogger(__METHOD__)->critical(
                'ElasticExportRakutenDE::log.criticalAbort', 
                "The update process was aborted to ensure the system's stability because the Rakuten API did not respond multiple times."
            );
        } catch (\Throwable $throwable) {
            $this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.cronError', [
                'error' => $throwable->getMessage(),
                'line' => $throwable->getLine(),
            ]);
        }
        $itemUpdateService->getClient()->closeConnections();
    }
}