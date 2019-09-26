<?php

namespace ElasticExportRakutenDE\Api;

use Plenty\Plugin\Log\Loggable;
use ElasticExportRakutenDE\Exceptions\EmptyResponseException;

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
     * @var int
     */
	private $emptyResponseErrorIterator = 0;
	
	private $curlHandles = [
	    self::EDIT_PRODUCT => null,
        self::EDIT_PRODUCT_VARIANT => null,
        self::EDIT_PRODUCT_MULTI_VARIANT => null,
    ];

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
     * @throws EmptyResponseException
     */
	public function call($endPoint, $httpRequestMethod, $content = [])
	{
		$response = '';
		
		try {
			$ch = $this->getCurlHandle($endPoint);

			switch ($httpRequestMethod) {
				case self::POST:
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
					break;
			}

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 0) {
                $this->emptyResponseErrorIterator++;
            } else {
                $this->emptyResponseErrorIterator = 0;
            }

            if ($this->emptyResponseErrorIterator == 5) {
                throw new EmptyResponseException();
            }

			$response = pluginApp(\SimpleXMLElement::class, [0 => $response, 1 => 0, 2 => false, 3 => "", 4 => false]);

			if ($response->success == "-1" && count($response->errors)) {
				if ($this->errorIterator == 100) {
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
		} catch (EmptyResponseException $exception) {
		    // forward the exception to abort the complete cron job
            throw $exception;
        } catch (\Throwable $throwable) {
            if ($this->errorIterator == 100) {
				$this->writeLogs();
			}

			$this->errorBatch[] = [
				'message'	=> $throwable->getMessage(),
				'line'	=> $throwable->getLine(),
                'response' => $response
			];

			$this->errorIterator++;
		}

		return $response;
	}

    /**
     * @param string
     * @return resource|false
     */
    private function getCurlHandle($endPoint)
    {
        $url = self::URL.$endPoint;
        if (array_key_exists($endPoint, $this->curlHandles)) {
            if (!isset($this->curlHandles[$endPoint])) {
                $this->curlHandles[$endPoint] = curl_init($url);
            }
            
            return $this->curlHandles[$endPoint];
        }
        
        return false;
    }
	
	public function closeConnections() {
	    try {
	        foreach ($this->curlHandles as $key => $curlHandle) {
	            if (!is_null($curlHandle)) {
	                curl_close($curlHandle);
                }
	            
	            if (!is_null($this->curlHandles[$key])) {
                    curl_close($this->curlHandles[$key]);
                }
            }
        } catch (\Throwable $throwable) {
	        $this->getLogger(__METHOD__)->logException($throwable);
        }
    }
	
	public function writeLogs()
	{
		if(is_array($this->errorBatch) && count($this->errorBatch)) {
			$this->getLogger(__METHOD__)->error('ElasticExportRakutenDE::log.apiError', [
				'errorList'	=> $this->errorBatch
			]);
		}

		$this->errorBatch = [];
		$this->errorIterator = 0;
	}
}