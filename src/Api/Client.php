<?php

namespace ElasticExportRakutenDE\Api;

use Plenty\Plugin\Log\Loggable;

/**
 * @class Client
 */
class Client
{
	use Loggable;

	const GET = '';
	const POST = 'POST';

	const URL = 'http://webservice.rakuten.de/merchants/';

	const EDIT_PRODUCT = 'products/editProduct';
	const EDIT_PRODUCT_VARIANT = 'products/editProductVariant';
	const EDIT_PRODUCT_MULTI_VARIANT = 'products/editProductMultiVariant';

	const PRODUCT_ART_NO = 'product_art_no';
	const VARIANT_ART_NO = 'variant_art_no';

	/**
	 * @var array
	 */
	private $errorBatch = [];

	/**
	 * @var int
	 */
	private $errorIterator = 0;

	/**
	 * ApiClient constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @param string $endPoint
	 * @param string $httpRequestMethod
	 * @param array $content
	 * @return \SimpleXMLElement
	 */
	public function call($endPoint, $httpRequestMethod, $content = [])
	{
		$response = '';
		$url = self::URL.$endPoint;

		try
		{
			$ch = curl_init($url);

			switch($httpRequestMethod)
			{
				case self::POST:
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
					break;
			}

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			curl_close($ch);

			$response = pluginApp(\SimpleXMLElement::class, [0 => $response, 1 => 0, 2 => false, 3 => "", 4 => false]);

			if($response->success == "-1" && count($response->errors))
			{
				if($this->errorIterator == 100)
				{
					$this->writeLogs();
				}
				
				$this->errorBatch[] = [
					'endpoint'          => $endPoint,
					'error code' 		=> $response->errors->error->code,
					'message'			=> $response->errors->error->message,
					'request content'	=> $content
				];
				
				$this->errorIterator++;
			}

		}
		catch (\Throwable $throwable)
		{
			if($this->errorIterator == 100)
			{
				$this->writeLogs();
			}
			
			$this->errorBatch[] = [
				'message'	=> $throwable->getMessage(),
				'line'	=> $throwable->getLine(),
			];

			$this->errorIterator++;
		}

		return $response;
	}
	
	public function writeLogs()
	{
		if(is_array($this->errorBatch) && count($this->errorBatch))
		{
			$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.apiError', [
				'errorList'	=> $this->errorBatch
			]);
		}

		$this->errorBatch = [];
		$this->errorIterator = 0;
	}
}