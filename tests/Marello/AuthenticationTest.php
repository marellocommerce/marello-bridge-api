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

use Marello\Api\Authentication;
use Marello\Api\MarelloException;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    const TEST_USER_NAME    = 'admin';
    const TEST_PASSWORD     = '1234';

    /**
     * @expectedException \Marello\Api\MarelloException
     * @expectedExceptionMessage No Username or Apikey specified
     */
    public function testAuthenticationFailedToSetup()
    {
        $authentication = new Authentication(null, null);
        $authentication->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function testAuthenticationHeaders()
    {
        $authentication = new Authentication(self::TEST_USER_NAME, self::TEST_PASSWORD);
        $headers = $authentication->getHeaders();
        $this->assertCount(3, $headers);
        $this->assertTrue(is_array($headers));

        $this->assertContains('Content-Type: application/json', $headers);
        $this->assertContains('Authorization: WSSE profile="UsernameToken"', $headers);

        $lastHeader = end($headers);
        $this->assertContains('X-WSSE: UsernameToken', $lastHeader);
        $this->assertContains('Username=', $lastHeader);
        $this->assertContains('PasswordDigest=', $lastHeader);
        $this->assertContains('Nonce=', $lastHeader);
        $this->assertContains('Created=', $lastHeader);
    }
}
