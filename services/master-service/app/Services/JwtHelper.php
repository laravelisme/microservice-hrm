<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;

class JwtHelper
{
    /**
     * Decode a raw JWT token string. Returns decoded payload object or null on failure.
     */
    public static function decodeToken(string $token): ?object
    {
        try {
            $secret = config('jwt.secret');
            $algo = config('jwt.algo', 'HS256');

            $decoded = JWT::decode($token, new Key($secret, $algo));

            return $decoded;
        } catch (ExpiredException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get decoded payload from current request. Returns object or null.
     */
    public static function getPayloadFromRequest(Request $request): ?object
    {
        // Prefer payload already decoded by middleware
        $payload = $request->attributes->get('jwt_payload');
        if ($payload) {
            return $payload;
        }

        // Fallback: decode bearer token
        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        return self::decodeToken($token);
    }

    /**
     * Return roles array from request token payload. Always returns array.
     */
    public static function getRolesFromRequest(Request $request): array
    {
        $payload = self::getPayloadFromRequest($request);
        if (! $payload) {
            return [];
        }

        $claims = (array) $payload;

        $roles = [];

        if (isset($claims['roles'])) {
            if (is_array($claims['roles'])) {
                $roles = $claims['roles'];
            } elseif (is_string($claims['roles'])) {
                $decoded = json_decode($claims['roles'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $roles = $decoded;
                } else {
                    $roles = [$claims['roles']];
                }
            }
        } elseif (isset($claims['role'])) {
            if (is_array($claims['role'])) {
                $roles = $claims['role'];
            } else {
                $roles = [$claims['role']];
            }
        }

        // normalize to string lower-case
        return array_map(fn($r) => strtolower((string) $r), $roles);
    }

    /**
     * Check if request token contains any of the given role(s).
     * $need can be string or array. Returns bool.
     */
    public static function hasRole(Request $request, string|array $need): bool
    {
        $needArr = is_array($need) ? $need : [$need];
        $needArr = array_map(fn($r) => strtolower((string) $r), $needArr);

        $roles = self::getRolesFromRequest($request);

        foreach ($needArr as $n) {
            if (in_array($n, $roles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get arbitrary claim value from request token payload.
     */
    public static function getClaim(Request $request, string $key, $default = null)
    {
        $payload = self::getPayloadFromRequest($request);
        if (! $payload) {
            return $default;
        }

        $claims = (array) $payload;

        return $claims[$key] ?? $default;
    }
}
