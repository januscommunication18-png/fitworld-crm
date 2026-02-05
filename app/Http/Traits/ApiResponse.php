<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'message' => $message,
            ],
            'errors' => [],
        ], $code);
    }

    protected function error(string $message = 'Error', array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'data' => null,
            'meta' => [
                'message' => $message,
            ],
            'errors' => $errors,
        ], $code);
    }
}
