<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTraits
{
    /**
     * Mengirimkan response sukses.
     *
     * @param mixed|null $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function successResponse(mixed $data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Mengirimkan response error.
     *
     * @param string $message
     * @param int $code
     * @param mixed|null $errors
     * @return JsonResponse
     */
    public function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }
}
