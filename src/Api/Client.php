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

	const PRODUCT_ART_NO = 'product_art_no';
	const VARIANT_ART_NO = 'variant_art_no';

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
		}
		catch (\Throwable $throwable)
		{
			$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.apiError', [
				'error' => $throwable->getMessage(),
				'line' => $throwable->getLine(),
			]);
		}

		return $response;
	}
}