<?php

namespace Overwatch\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Doctrine\ORM\EntityManager;

/**
 * ApiAuthenticator
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class ApiAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface {
    const USER_ID = "x-api-user";
    const TIMESTAMP = "x-api-timestamp";
    const TOKEN = "x-api-token";
    
    private $em;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    /**
     * @param string $providerKey
     */
    public function createToken(Request $request, $providerKey) {
        if (
            !$request->headers->has(self::USER_ID) ||
            !$request->headers->has(self::TIMESTAMP) ||
            !$request->headers->has(self::TOKEN)
        ) {
            throw new BadCredentialsException('API credentials invalid. The user ID, timestamp and token should given.');
        }
        
        if (!is_numeric($request->headers->get(self::USER_ID))) {
            throw new BadCredentialsException('API credentials invalid. The user ID should be an integer.');
        }
        
        if (!is_numeric($request->headers->get(self::TIMESTAMP))) {
            throw new BadCredentialsException('API credentials invalid. The timestamp should be an integer.');
        }
        
        return new PreAuthenticatedToken(
            'anon.',
            [
                self::USER_ID => $request->headers->get(self::USER_ID),
                self::TIMESTAMP => $request->headers->get(self::TIMESTAMP),
                self::TOKEN => $request->headers->get(self::TOKEN),
            ],
            $providerKey
        );
    }
    
    /**
     * @param string $providerKey
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
        $credentials = $token->getCredentials();
        
        if (abs(time() - (int) $credentials[self::TIMESTAMP]) > 60) {
            throw new AuthenticationException('API credentials invalid. The timestamp is more than 60 seconds old.');
        }
        
        $user = $this->em->find("OverwatchUserBundle:User", $credentials[self::USER_ID]);
        
        if ($user === null || $user->isLocked()) {
            throw new AuthenticationException('API credentials invalid. User not found.');
        }
        
        $apiToken = hash_hmac(
            "sha256",
            "timestamp=" . $credentials[self::TIMESTAMP],
            $user->getApiKey()
        );
        
        if ($apiToken !== $credentials[self::TOKEN]) {
            throw new AuthenticationException("API credentials invalid. Token verification failed.");
        }
        
        return new PreAuthenticatedToken(
            $user,
            $token->getCredentials(),
            $providerKey,
            $user->getRoles()
        );
    }
    
    /**
     * @param string $providerKey
     */
    public function supportsToken(TokenInterface $token, $providerKey) {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return new JsonResponse($exception->getMessage(), JsonResponse::HTTP_UNAUTHORIZED);
    }
}
