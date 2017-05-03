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
namespace Marello\Api\Curl;

use Marello\Api\MarelloException;

class Response
{
    protected $responseData = [];

    /**
     * @param array $curlResult
     * @return Response
     * @throws MarelloException
     */
    public static function create(array $curlResult)
    {
        if (empty($curlResult)) {
            throw new MarelloException('Could not create responds from empty array');
        }

        $response = new self();
        $response->setData($curlResult);

        return $response;
    }

    public function getErrorMessage()
    {
        return $this->getData('error');
    }

    protected function setData($data)
    {
        $this->responseData = $data;
    }

    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->responseData;
        }

        if (!isset($this->responseData[$key]) || empty($this->responseData[$key])) {
            return null;
        }

        return $this->responseData[$key];
    }
}
