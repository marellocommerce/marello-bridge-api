<?php
/**
 * Marello
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is published at http://opensource.org/licenses/osl-3.0.php.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@marello.com so we can send you a copy immediately
 *
 * @category  Marello
 * @package   Api
 * @copyright Copyright 2016 Marello (http://www.marello.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Marello\Tests\Stub;

use Marello\Api\Client;
use Marello\Api\MarelloException;

class ClientStub extends Client
{
    /** @var string $requestUrl */
    protected $requestUrl;

    /** @var array $curlResponse */
    protected $curlResponse;

    /** @var string $curlError */
    protected $curlError;

    /** @var bool $result */
    protected $result = true;

    /**
     * Stub the callServer method to setup results and errors
     * @param string $url url to call
     * @param array|null $data data for the request
     * @param array $curlOptions
     * @throws MarelloException
     * @return mixed $result
     */
    public function callServer($url, $curlOptions, array $data = [])
    {
        $ch = curl_init($url);
        $request = $this->createRequest($ch, $curlOptions, $url);
        $this->setLastRequestHeaders($request);
        curl_close($ch);

        $result = $this->result; // normally the curl call is being executed

        $curlInfo = $this->curlResponse;
        if ($result === false) {
            $curlInfo['error'] = $this->curlError;
        } else {
            $curlInfo['body'] = $result;
            $curlInfo['url'] = $url;
        }

        $response = $this->createResponse($curlInfo);
        $this->setLastResponse($response);
        return $this->lastResponse;
    }

    /**
     * @param array $data
     */
    public function setFakeResponse(array $data)
    {
        $this->curlResponse = $data;
    }

    /**
     * @param $message
     */
    public function setCurlError($message)
    {
        $this->curlError = $message;
    }

    public function setResult($bool)
    {
        $this->result = $bool;
    }

    public function getApiUrl()
    {
        return $this->requestUrl;
    }
}
