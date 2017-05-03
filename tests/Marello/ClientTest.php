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
 * @package   Tests
 * @copyright Copyright Marello (http://www.marello.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/
namespace Marello\Tests;

use Marello\Api\Client;
use Marello\Tests\Stub\ClientStub;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const TEST_URL = 'http://demo.marello.com/';
    const USERNAME = 'admin';
    const PASSWORD = 'd07009855780a53f025f0cfea3eb081ba124c0c8';

    /** @var ClientStub $client */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->client = new ClientStub(self::TEST_URL);
        $this->client->setAuth([
            'username' => self::USERNAME,
            'api_key' => self::PASSWORD
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->client = null;
    }

    /**
     * @expectedException \Marello\Api\MarelloException
     * @expectedExceptionMessage No api url provided
     */
    public function testClientFailedToSetupUrl()
    {
        $client = new Client(null);
        $client->pingInstance();
    }

    /**
     * @expectedException \Marello\Api\MarelloException
     * @expectedExceptionMessage No credentials provided. Please call Client::setAuth() before making requests.
     */
    public function testClientFailedToSetupAuth()
    {
        $client = new Client(self::TEST_URL);
        $client->pingInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function testClientSuccesfulPingInstance()
    {
        $this->client->setFakeResponse([
            'http_code' => 200,
            'content_type' => 'application/json',
            'body' => json_encode(['response' => 'is OK!'])
        ]);

        $this->assertTrue($this->client->pingInstance());
        $this->assertEquals(Client::HTTP_CODE_OK, $this->client->getResponseCode());
        $this->assertNotEmpty($this->client->getLastResponse());
    }

    /**
     * {@inheritdoc}
     */
    public function testRestGetCallWithoutQuery()
    {
        $this->client->setFakeResponse([
            'http_code' => 200,
            'content_type' => 'application/json',
            'body' => json_encode(['response' => 'is OK!'])
        ]);

        $result = $this->client->restGet('/users');
        $this->doLastResponseAndHeadersTests($result);
    }

    /**
     * {@inheritdoc}
     */
    public function testRestGetCallWithQuery()
    {
        $this->client->setFakeResponse([
            'http_code' => 200,
            'content_type' => 'application/json',
            'body' => json_encode(['response' => 'is OK!'])
        ]);

        $result = $this->client->restGet('/test', ['page' => 2, 'limit' => 200]);
        $this->doLastResponseAndHeadersTests($result);
        $this->assertContains('page=2&limit=200', $result->getData('url'));
    }

    /**
     * @expectedException \Marello\Api\MarelloException
     * @expectedExceptionMessage Query cannot be empty when a POST call is made.
     */
    public function testRestFailPostCall()
    {
        $this->client->restPost('/test', []);
    }

    /**
     * {@inheritdoc}
     */
    public function testRestPostCall()
    {
        $payload = ['customer' => 2, 'name' => 'john'];
        $result = $this->client->restPost('/test', $payload);

        $this->doLastResponseAndHeadersTests($result);
        $this->assertContains(json_encode($payload), $this->client->getLastRequestHeaders()->getData());
    }

    /**
     * @expectedException \Marello\Api\MarelloException
     * @expectedExceptionMessage Query cannot be empty when a PUT call is made.
     */
    public function testRestFailPutCall()
    {
        $this->client->restPut('/test', []);
    }

    /**
     * {@inheritdoc}
     */
    public function testRestPutCall()
    {
        $payload = ['order' => 2, 'lastName' => 'Doe'];
        $result = $this->client->restPost('/test', $payload);

        $this->doLastResponseAndHeadersTests($result);
        $this->assertContains(json_encode($payload), $this->client->getLastRequestHeaders()->getData());
    }

    /**
     * @expectedException \Marello\Api\MarelloException
     * @expectedExceptionMessage Query cannot be empty when a DELETE call is made.
     */
    public function testRestFailDeleteCall()
    {
        $this->client->restDelete('/test', []);
    }

    /**
     * {@inheritdoc}
     */
    public function testRestDeleteCall()
    {
        $payload = ['address' => 2];
        $result = $this->client->restPost('/test', $payload);

        $this->doLastResponseAndHeadersTests($result);
        $this->assertContains(json_encode($payload), $this->client->getLastRequestHeaders()->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function testCallWithErrorResponse()
    {
        $this->client->setFakeResponse([
            'http_code' => 404,
            'content_type' => 'application/json',
        ]);
        $this->client->setResult(false);
        $errorMessage = 'Well this ain\'t good...';
        $this->client->setCurlError($errorMessage);

        $result = $this->client->restGet('/darn');
        $this->doLastResponseAndHeadersTests($result);
        $this->assertEquals(404, $this->client->getResponseCode());

        $this->assertArrayHasKey('error', $result->getData());
        $this->assertArrayNotHasKey('body', $result->getData());
        $this->assertEquals($errorMessage, $result->getErrorMessage());
    }

    /**
     * {@inheritdoc}
     * @param $result
     */
    protected function doLastResponseAndHeadersTests($result)
    {
        $this->assertNotEmpty($this->client->getLastResponse());
        $this->assertEquals($this->client->getLastResponse(), $result);
        $this->assertContains(self::TEST_URL, $this->client->getLastRequestHeaders()->getData());
    }
}
