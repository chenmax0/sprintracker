<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Security;

use Doctrine\DBAL\Connection;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\CookieTokenExtractor;
use Symfony\Component\HttpFoundation\Request;

/**
 * Best-effort "who is this?" for the public tracking endpoint. The firewall
 * can't be used here: a stale or expired BEARER cookie (the JWT lives one hour)
 * would be rejected with a 401, and tracking would silently drop exactly the
 * returning visitors we want to count. A missing or invalid token just means
 * "anonymous" instead.
 */
final class OptionalUserIdResolver
{
    public function __construct(
        private JWTTokenManagerInterface $jwt,
        private Connection $connection,
    ) {
    }

    public function resolve(Request $request): ?string
    {
        $token = (new CookieTokenExtractor('BEARER'))->extract($request)
            ?: (new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization'))->extract($request);

        if (!is_string($token) || '' === $token) {
            return null;
        }

        try {
            $payload = $this->jwt->parse($token);
        } catch (JWTDecodeFailureException) {
            return null;
        }

        $email = $payload[$this->jwt->getUserIdClaim()] ?? null;
        if (!is_string($email)) {
            return null;
        }

        $id = $this->connection->fetchOne('SELECT id FROM "user" WHERE email = :email', ['email' => $email]);

        return false === $id ? null : (string) $id;
    }
}
