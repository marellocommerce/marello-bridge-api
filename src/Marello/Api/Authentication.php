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

class Authentication
{
    /** @var string $username */
    private $username;

    /** @var string $apiKey */
    private $apiKey;

    /** @var array $headers */
    protected $headers;

    /**
     * Authentication constructor.
     * @param $username
     * @param $apiKey
     */
    public function __construct($username, $apiKey)
    {
        $this->setupAuthenticationCredentials($username, $apiKey);
    }

    /**
     * @param $username
     * @param $apiKey
     * @throws MarelloException
     */
    private function setupAuthenticationCredentials($username, $apiKey)
    {
        if (!$username || !$apiKey) {
            throw new MarelloException('No Username or Apikey specified');
        }
        $this->username = $username;
        $this->apiKey   = $apiKey;
    }

    /**
     * encode raw password
     * @param $raw
     * @return string
     */
    private function encodePassword($raw, $salt)
    {
        $salted = $this->mergePasswordAndSalt($raw, $salt);
        $digest = hash('sha1', $salted, true);
        return base64_encode($digest);
    }

    /**
     * @param $password
     * @param $salt
     * @return string
     * @throws \InvalidArgumentException
     */
    private function mergePasswordAndSalt($password, $salt)
    {
        if (empty($salt)) {
            return $password;
        }

        if (false !== strrpos($salt, '{') || false !== strrpos($salt, '}')) {
            throw new \InvalidArgumentException('Cannot use { or } in salt.');
        }

        return $password.'{'.$salt.'}';
    }

    /**
     * Prepare headers for the authentication
     * @return array
     */
    public function getHeaders()
    {
        $created = date('c');
        $nonce  = $this->generateNonce();
        $passwordDigest = $this->generatePasswordDigest($nonce, $created);

        $wsseProfile = sprintf(
            'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $this->username,
            $passwordDigest,
            $nonce,
            $created
        );

        return array(
            'Content-Type: application/json',
            'Authorization: WSSE profile="UsernameToken"',
            $wsseProfile
        );
    }

    /**
     * Generate nonce
     * @return string
     */
    private function generateNonce()
    {
        $prefix = gethostname();
        return base64_encode(substr(md5(uniqid($prefix . '_', true)), 0, 16));
    }

    /**
     * Generate password digest
     * @param $nonce
     * @param $created
     * @return string
     */
    private function generatePasswordDigest($nonce, $created)
    {
        $rawPass = base64_decode($nonce) . $created . $this->apiKey;
        return $this->encodePassword($rawPass, '');
    }
}
