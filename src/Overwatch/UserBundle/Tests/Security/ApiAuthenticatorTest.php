<?php

namespace Overwatch\UserBundle\Security;

use Overwatch\UserBundle\Entity\User;
use Overwatch\UserBundle\Security\ApiAuthenticator;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * ApiAuthenticatorTest
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class ApiAuthenticatorTest extends \PHPUnit_Framework_TestCase {
    const PROVIDER_KEY = "overwatch_test";
    
    private $apiAuth;
    private $user;
    
    public function setUp() {
        $fakeEm = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $this->user = $this->createUserMock();
        
        $fakeEm
            ->method('find')
            ->will(
                $this->returnValueMap([
                    ["OverwatchUserBundle:User", 1, null, null, $this->user],
                    ["OverwatchUserBundle:User", 2, null, null, null]
                ])
            )
        ;
        
        $this->apiAuth = new ApiAuthenticator($fakeEm);
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\BadCredentialsException
     * @expectedExceptionMessage API credentials invalid. The user ID, timestamp and token should given.
     */
    public function testCreateTokenNoHeaders() {
        $this->apiAuth->createToken($this->createRequestMock(), self::PROVIDER_KEY);
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\BadCredentialsException
     * @expectedExceptionMessage API credentials invalid. The user ID, timestamp and token should given.
     */
    public function testCreateTokenTwoHeaders() {
        $this->apiAuth->createToken($this->createRequestMock([
            ApiAuthenticator::USER_ID => null
        ]), self::PROVIDER_KEY);
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\BadCredentialsException
     * @expectedExceptionMessage API credentials invalid. The user ID should be an integer.
     */
    public function testCreateTokenInvalidUserId() {
        $this->apiAuth->createToken($this->createRequestMock([
            ApiAuthenticator::USER_ID => "overwatch_test"
        ]), self::PROVIDER_KEY);
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\BadCredentialsException
     * @expectedExceptionMessage API credentials invalid. The timestamp should be an integer.
     */
    public function testCreateTokenInvalidTimestamp() {
        $this->apiAuth->createToken($this->createRequestMock([
            ApiAuthenticator::TIMESTAMP => "overwatch_test"
        ]), self::PROVIDER_KEY);
    }
    
    public function testCreateToken() {
        $token = $this->apiAuth->createToken($this->createRequestMock([
            ApiAuthenticator::TIMESTAMP => "111"
        ]), self::PROVIDER_KEY);
        
        $this->assertInstanceOf("Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken", $token);
        $this->assertEquals("anon.", $token->getUser());
        $this->assertEquals(self::PROVIDER_KEY, $token->getProviderKey());
        $this->assertEquals([
            ApiAuthenticator::USER_ID => 1,
            ApiAuthenticator::TIMESTAMP => "111",
            ApiAuthenticator::TOKEN => 'abc123'
        ], $token->getCredentials());
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage API credentials invalid. The timestamp is more than 60 seconds old.
     */
    public function testAuthenticateTokenOldTimestamp() {
        $this->apiAuth->authenticateToken(
            $this->createToken([
                ApiAuthenticator::TIMESTAMP => time() - 61
            ]),
            $this->createUserProviderMock(),
            self::PROVIDER_KEY
        );
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage API credentials invalid. The timestamp is more than 60 seconds old.
     */
    public function testAuthenticateTokenFutureTimestamp() {
        $this->apiAuth->authenticateToken(
            $this->createToken([
                ApiAuthenticator::TIMESTAMP => time() + 61
            ]),
            $this->createUserProviderMock(),
            self::PROVIDER_KEY
        );
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage API credentials invalid. User not found.
     */
    public function testAuthenticateTokenNoUser() {
        $this->apiAuth->authenticateToken(
            $this->createToken([
                ApiAuthenticator::USER_ID => 2
            ]),
            $this->createUserProviderMock(),
            self::PROVIDER_KEY
        );
    }
    
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage API credentials invalid. Token verification failed
     */
    public function testAuthenticateTokenBadToken() {
        $this->apiAuth->authenticateToken(
            $this->createToken([]),
            $this->createUserProviderMock(),
            self::PROVIDER_KEY
        );
    }
    
    public function testAuthenticateToken() {
        $timestamp = time();
        $apiToken = hash_hmac(
            "sha256",
            "timestamp=" . $timestamp,
            $this->user->getApiKey()
        );
        
        $token = $this->apiAuth->authenticateToken(
            $this->createToken([
                ApiAuthenticator::TIMESTAMP => $timestamp,
                ApiAuthenticator::TOKEN => $apiToken
            ]),
            $this->createUserProviderMock(),
            self::PROVIDER_KEY
        );
        
        $this->assertInstanceOf("Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken", $token);
        $this->assertEquals($this->user, $token->getUser());
        $this->assertEquals(self::PROVIDER_KEY, $token->getProviderKey());
        $this->assertEquals([
            ApiAuthenticator::USER_ID => 1,
            ApiAuthenticator::TIMESTAMP => $timestamp,
            ApiAuthenticator::TOKEN => $apiToken
        ], $token->getCredentials());
        $this->assertEquals($this->user->getRoles()[0], $token->getRoles()[0]->getRole());
    }
    
    public function testSupportsTokenWrongProviderKey() {
        $this->assertFalse($this->apiAuth->supportsToken($this->createToken([]), "totally_not_correct_provider_key"));
    }
    
    public function testSupportsTokenWrongType() {
        $this->assertFalse($this->apiAuth->supportsToken(new UsernamePasswordToken("anon", [], self::PROVIDER_KEY), self::PROVIDER_KEY));
    }
    
    public function testSupportsToken() {
        $this->assertTrue($this->apiAuth->supportsToken($this->createToken([]), self::PROVIDER_KEY));
    }
    
    public function testOnAuthenticationFailureWithAuthenticationException() {
        $message = "Bacon ipsum dolor sit amet";
        
        $response = $this->apiAuth->onAuthenticationFailure(
            $this->createRequestMock(),
            new AuthenticationException($message)
        );
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(json_encode($message), $response->getContent());
        $this->assertEquals(401, $response->getStatusCode());
    }
    
    public function testOnAuthenticationFailureWithBadCredentialsException() {
        $message = "Bacon ipsum dolor sit amet";
        
        $response = $this->apiAuth->onAuthenticationFailure(
            $this->createRequestMock(),
            new BadCredentialsException($message)
        );
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(json_encode($message), $response->getContent());
        $this->assertEquals(401, $response->getStatusCode());
    }
    
    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function createRequestMock(array $headers = null) {
        $headers = $this->mergeHeaders($headers);
        
        $headerBag = new HeaderBag;
        $headerBag->add($headers);
        
        $fakeReq = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->disableOriginalConstructor()->getMock();
        $fakeReq->headers = $headerBag;
        
        return $fakeReq;
    }
    
    private function createToken(array $headers = null) {
        return new PreAuthenticatedToken(
            'anon.',
            $this->mergeHeaders($headers),
            self::PROVIDER_KEY
        );
    }
    
    private function createUserMock() {
        $user = new User;
        $user
            ->setEmail("overwatch.test@example.com")
            ->resetApiKey()
        ;
        
        return $user;
    }
    
    /**
     * @return \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    private function createUserProviderMock() {
        return $this->getMockBuilder('FOS\UserBundle\Security\UserProvider')->disableOriginalConstructor()->getMock();
    }
    
    private function mergeHeaders(array $headers = null) {
        if ($headers === null) {
            $headers = [];
        } else {
            $headers = array_merge([
                ApiAuthenticator::USER_ID => 1,
                ApiAuthenticator::TIMESTAMP => time(),
                ApiAuthenticator::TOKEN => 'abc123'
            ], $headers);
            
            foreach ($headers as $header => $headerValue) {
                if ($headerValue === null) {
                    unset($headers[$header]);
                }
            }
        }
        
        return $headers;
    }
}
