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

class RequestHeader
{
    protected $headerData = [];

    /**
     * @param array $curlData
     * @return RequestHeader
     * @throws MarelloException
     */
    public static function create(array $curlData)
    {
        if (empty($curlData)) {
            throw new MarelloException('Could not create headers from empty array');
        }

        $header = new self();
        $header->setData($curlData);

        return $header;
    }

    protected function setData($data)
    {
        $this->headerData = $data;
    }

    public function getData($key = null)
    {
        if (!$key) {
            return $this->headerData;
        }

        if (!isset($this->headerData[$key]) || empty($this->headerData[$key])) {
            return null;
        }

        return $this->headerData[$key];
    }
}
