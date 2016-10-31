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

use Marello\Api\MarelloException;
use Marello\Api\Authentication;

class Client
{
    const HTTP_CODE_UNAUTHORIZED = 401;

    const CALL_PATH_REST_API = 'api/rest';

    protected $defaultFilters = ['page', 'limit'];

    /** @var string $version */
    protected $version = 'latest';

    /** @var array $version */
    protected $credentials = [];

    /** @var string $apiUrl */
    protected $apiUrl;
    
    /** @var int $responseCode */
    protected $responseCode;
    
    /**
     * Client constructor.
     * @param $apiUrl
     */
    public function __construct($apiUrl)
    {
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
     * @return bool
     */
    public function pingUsers()
    {
        $this->restGet("/users");

        if ($this->getResponseCode() === self:: HTTP_CODE_UNAUTHORIZED) {
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
        return $this->responseCode;
    }

    /**
     * Set api url including callpath and version
     * @param $url
     * @throws MarelloException
     */
    public function setApiUrl($url)
    {
        if (!$url) {
            throw new MarelloException('No api url provided');
        }

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
        $skip = false;
        if (strpos($path, 'filter') !== false || strpos($path, 'filter') === 0) {
            $skip = true;
            $path = str_replace('filter', '', $path);
        }

        if ($path[0] != '/' && $uri[strlen($uri)-1] != '/' && !$skip) {
            $path = '/' . $path;
        }

        return $uri . $path;
    }

    /**
     * Set curl data and execute
     * @param string $url url to call
     * @param array|null $data data for the request
     * @param array $curlOptions
     * @return mixed $result
     */
    private function callServer($url, $curlOptions, array $data = [])
    {
        $ch = curl_init();
        if (is_array($curlOptions)) {
            curl_setopt_array($ch, $curlOptions);
        }

        $curlData = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->getAuthHeaders(),
            CURLOPT_HEADER => 0,
            CURLOPT_FAILONERROR => true,
            CURLOPT_URL => $url
        ];

        curl_setopt_array($ch, $curlData);

        $result = curl_exec($ch);
        
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->setResponseCode($responseCode);

        if ($result === false) {
            $result = curl_error($ch);
        }

        curl_close($ch);

        return $result;
    }
    
    /**
     * Get Authentication Headers
     * @return mixed
     * @throws MarelloException
     */
    private function getAuthHeaders()
    {
        $auth = new Authentication($this->credentials['username'], $this->credentials['api_key']);
        
        return $auth->getHeaders();
    }

    /**
     * Set response code
     * @param $responseCode
     */
    private function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
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

        $uri = null;
        foreach ($params as $param => $value) {
            if (in_array($param, $this->defaultFilters)) {
                $value = sprintf('filter?%s=%s', $param, $value);
            }

            $uri = $this->prepareRestUri($value, $url);
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
            throw new MarelloException('Data must be specified if a POST call is made');
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
            throw new MarelloException('Data must be specified if a PUT call is made');
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
     * @throws MarelloException
     */
    public function restDelete($path, array $query = [])
    {
        if (empty($query)) {
            throw new MarelloException('Data must be specified if a DELETE call is made');
        }
        /** @todo: Implement restDelete logic in next release */
    }
}
