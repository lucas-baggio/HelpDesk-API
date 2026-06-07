<?php

namespace App\Shared\Http\Concerns;

use App\Shared\Http\ApiResponse;
use App\Shared\Http\HttpStatus;
use Illuminate\Http\JsonResponse;

trait RespondsWithApiEnvelope
{
    protected function success(
        mixed $data = null,
        string $message = 'Operation completed successfully',
        int $status = HttpStatus::OK,
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status);
    }

    protected function created(
        mixed $data = null,
        string $message = 'Resource created successfully',
    ): JsonResponse {
        return ApiResponse::created($data, $message);
    }

    protected function deleted(
        string $message = 'Resource deleted successfully',
    ): JsonResponse {
        return ApiResponse::deleted($message);
    }

    protected function error(
        string $message,
        array $errors = [],
        int $status = HttpStatus::BAD_REQUEST,
    ): JsonResponse {
        return ApiResponse::error($message, $errors, $status);
    }

    protected function validation(
        array $errors,
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return ApiResponse::validation($errors, $message);
    }
}
