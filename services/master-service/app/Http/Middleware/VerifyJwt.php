<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class VerifyJwt
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        try {
            $secret = config('jwt.secret');
            $algo = config('jwt.algo', 'HS256');

            $decoded = JWT::decode(
                $token,
                new Key($secret, $algo)
            );

            $request->attributes->set('jwt_payload', $decoded);

        } catch (ExpiredException $e) {

            return response()->json([
                'message' => 'Token sudah kadaluarsa'
            ], 401);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Token tidak valid'
            ], 401);
        }

        return $next($request);
    }
}
