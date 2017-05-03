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
namespace Marello\Api;

use Marello\Api\Curl\RequestHeader;
use Marello\Api\Curl\Response;

class Client
{
    const HTTP_CODE_OK = 200;
    const CALL_PATH_REST_API = 'api/rest';

    /** @var string $version */
    protected $version = 'latest';

    /** @var array $credentials */
    protected $credentials = [];

    /** @var string $apiUrl */
    protected $apiUrl;

    /** @var int $responseCode */
    protected $responseCode;

    /** @var Response $lastResponse */
    protected $lastResponse;

    /** @var  $lastRequestHeaders */
    protected $lastRequestHeaders;

    /**
     * Marello_Api_Client constructor.
     * @param $apiUrl
     * @throws MarelloException
     */
    public function __construct($apiUrl)
    {
        if (!$apiUrl) {
            throw new MarelloException('No api url provided');
        }

        $this->setApiUrl($apiUrl);
    }

    /**
     * Set credentials for authentication
     * [
     *  'username' => 'username',
     *  'api_key' => 'apiKey',
     * ]
     *
     *
     * @param array $credentials
     * @throws MarelloException
     */
    public function setAuth(array $credentials)
    {
        if ((!isset($credentials['username']) && empty($credentials['username']))
            || (!isset($credentials['api_key']) && empty($credentials['api_key']))) {
            throw new MarelloException('Username of Apikey not specified.');
        }

        $this->credentials = $credentials;
    }

    /**
     * validate for a connection/authorization with Marello API
     * @deprecated use pingInstance() instead
     * @return bool
     */
    public function pingUsers()
    {
        return $this->pingInstance();
    }

    /**
     * validate for a connection/authorization with Marello API
     * @return bool
     */
    public function pingInstance()
    {
        $this->restGet("/users");

        if ($this->getResponseCode() !== self::HTTP_CODE_OK) {
            return false;
        }

        return true;
    }

    /**
     * Get response code from request
     * @return int
     */
    public function getResponseCode()
    {
        return $this->getLastResponse()->getData('http_code');
    }

    /**
     * Set api url including callpath and version
     * @param string $url
     */
    public function setApiUrl($url)
    {
        $this->apiUrl = $url . self::CALL_PATH_REST_API . DIRECTORY_SEPARATOR . $this->version;
    }

    /**
     * Prepare Rest uri for call
     * @param $path
     * @param $baseUri
     * @return string uri
     */
    private function prepareRestUri($path, $baseUri = null)
    {
        $uri = (is_null($baseUri)) ? $this->apiUrl : $baseUri;
        if ($path[0] != '/' && $uri[strlen($uri)-1] != '/') {
            $path = '/' . $path;
        }

        return $uri . $path;
    }

    /**
     * Set curl data and execute
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
        $result = curl_exec($ch);

        $curlInfo = curl_getinfo($ch);
        if ($result === false) {
            $curlInfo['error'] = curl_error($ch);
        } else {
            $curlInfo['body'] = $result;
        }

        curl_close($ch);

        $response = $this->createResponse($curlInfo);
        $this->setLastResponse($response);
        return $this->lastResponse;
    }

    /**
     * @param $info
     * @return Response
     */
    public function createResponse($info)
    {
        return Response::create($info);
    }

    /**
     * @param Response $response
     */
    public function setLastResponse(Response $response)
    {
        $this->lastResponse = $response;
    }

    /**
     * @param $handle
     * @param $data
     * @param $url
     * @return RequestHeader $curlData
     */
    public function createRequest($handle, $data, $url)
    {
        $curlData = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->getAuthHeaders(),
            CURLOPT_HEADER => 0,
            CURLOPT_FAILONERROR => true,
            CURLOPT_URL => $url
        ];

        if (is_array($data)) {
            $curlData = $curlData + $data;
        }

        curl_setopt_array($handle, $curlData);

        return RequestHeader::create($curlData);
    }

    /**
     * @param RequestHeader $headers
     */
    public function setLastRequestHeaders(RequestHeader $headers)
    {
        $this->lastRequestHeaders = $headers;
    }

    /**
     * Get Authentication Headers
     * @return mixed
     * @throws MarelloException
     */
    private function getAuthHeaders()
    {
        if ((!isset($this->credentials['username']) && empty($this->credentials['username']))
            || (!isset($this->credentials['api_key']) && empty($this->credentials['api_key']))) {
            throw new MarelloException(
                'No credentials provided. Please call Client::setAuth() before making requests.'
            );
        }

        $auth = new Authentication($this->credentials['username'], $this->credentials['api_key']);

        return $auth->getHeaders();
    }

    /**
     * Get request headers from last cURL session
     * @return int
     */
    public function getLastRequestHeaders()
    {
        return $this->lastRequestHeaders;
    }

    /**
     * Get last response from last cURL session
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Returns if call made has produced an error
     * @return boolean|null
     */
    public function hasError()
    {
        return ($this->lastResponse->getData('error'));
    }

    /**
     * Prepare get params for request
     * array('paramname' => 'paramvalue')
     * => <base_uri>/<path>/paramvalue
     * @param string $url
     * @param array $params
     * @return string $uri
     */
    private function prepareGetParams($url, array $params)
    {
        if (empty($params)) {
            return $url;
        }

        if (array_key_exists('id', $params)) {
            $uri = $this->prepareRestUri($params['id'], $url);
        } else {
            $uriParams = sprintf('?%s', http_build_query($params));
            $uri = $this->prepareRestUri($uriParams, $url);
        }

        return $uri;
    }

    /**
     * REST GET call
     * @param string $path
     * @param array $query
     * @return mixed
     */
    public function restGet($path, array $query = [])
    {
        $url = $this->prepareRestUri($path);
        // add query params to url
        $fullUrl = $this->prepareGetParams($url, $query);

        return $this->callServer($fullUrl, null, $query);
    }

    /**
     * REST POST call
     * @param $path
     * @param array $query
     * @return mixed
     * @throws MarelloException
     */
    public function restPost($path, array $query = [])
    {
        if (empty($query)) {
            throw new MarelloException('Query cannot be empty when a POST call is made.');
        }

        $url = $this->prepareRestUri($path);
        $curlOptions = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($query)
        ];

        return $this->callServer($url, $curlOptions, $query);
    }

    /**
     * REST PUT call
     * @param $path
     * @param array|null $query
     * @return mixed
     * @throws MarelloException
     */
    public function restPut($path, array $query = [])
    {
        if (empty($query)) {
            throw new MarelloException('Query cannot be empty when a PUT call is made.');
        }

        $url = $this->prepareRestUri($path);
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($query)
        ];

        return $this->callServer($url, $curlOptions, $query);
    }

    /**
     * REST DELETE call
     * @param $path
     * @param array $query
     * @return mixed
     * @throws MarelloException
     */
    public function restDelete($path, array $query = [])
    {
        if (empty($query)) {
            throw new MarelloException('Query cannot be empty when a DELETE call is made.');
        }

        $url = $this->prepareRestUri($path);
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => json_encode($query)
        ];

        return $this->callServer($url, $curlOptions, $query);
    }
}
