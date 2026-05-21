<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standardized response builder for all API endpoints
 * Ensures consistent response format across the entire application
 */
class ApiResponse
{
    public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function created($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function updated($data = null, string $message = 'Updated successfully'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    public static function deleted(string $message = 'Deleted successfully'): JsonResponse
    {
        return self::success(null, $message, 200);
    }

    public static function error(string $message, int $code = 400, $details = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($details) {
            $response['details'] = $details;
        }

        return response()->json($response, $code);
    }

    public static function badRequest(string $message, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? 'Invalid request',
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, 400);
    }

    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function conflict(string $message = 'Conflict', $details = null): JsonResponse
    {
        return self::error($message, 409, $details);
    }

    public static function unprocessable(string $message, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? 'Unprocessable entity',
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, 422);
    }

    public static function tooManyRequests(string $message = 'Too many requests'): JsonResponse
    {
        return self::error($message, 429);
    }

    public static function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return self::error($message, 500);
    }

    public static function paginated($items, $total, $perPage, $currentPage, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => ceil($total / $perPage),
                'from' => ($currentPage - 1) * $perPage + 1,
                'to' => min($currentPage * $perPage, $total),
            ],
        ], 200);
    }
}
