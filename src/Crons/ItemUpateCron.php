<?php

namespace ElasticExportRakutenDE\Crons;

use ElasticExportRakutenDE\Services\ItemUpdateService;
use Plenty\Plugin\Log\Loggable;

/**
 * @class ItemUpateCron
 */
class ItemUpateCron
{
	use Loggable;

	/**
	 * ItemUpateCron constructor.
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
			$itemUpdateService->generateContent();
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