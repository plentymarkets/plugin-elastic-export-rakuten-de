<?php

namespace ElasticExportRakutenDE\Crons;

use ElasticExportRakutenDE\Services\ApiClient;

/**
 * @class ItemUpateCron
 */
class ItemUpateCron
{
	/**
	 * @var ApiClient
	 */
	private $apiClient;

	/**
	 * ItemUpateCron constructor.
	 *
	 * @param ApiClient $apiClient
	 */
	public function __construct(ApiClient $apiClient)
	{
		$this->apiClient = $apiClient;
	}

	public function handle()
	{

	}
}