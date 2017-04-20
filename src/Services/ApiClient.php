<?php

namespace ElasticExportRakutenDE\Services;

/**
 * @class ApiClient
 */
class ApiClient
{
	use \Plenty\Plugin\Log\Loggable;

	const GET = '';
	const POST = '';

	const URL = 'http://webservice.rakuten.de/merchants/';

	const EDIT_PRODUCT = 'products/editProduct';
	const EDIT_PRODUCT_VARIANT = 'products/editProductVariant';

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
	 * @return string
	 */
	public function call($endPoint, $httpRequestMethod, $content = [])
	{
		$response = '';
		$url = self::URL.$endPoint;

		try
		{
			$ch = curl_init($url);
//			curl_setopt($ch, CURLOPT_URL, $url);

			switch($httpRequestMethod)
			{
				case self::POST:
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
					curl_setopt($ch, CURLOPT_POST, true);
					break;
			}


			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			curl_close($ch);
		}
		catch (\Throwable $exception)
		{
			$this->getLogger(__METHOD__)->error('');		// TODO
		}

		return $response;
	}
}