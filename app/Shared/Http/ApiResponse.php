<?php

namespace App\Shared\Http;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use stdClass;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Operation completed successfully',
        int $status = HttpStatus::OK,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::resolveData($data),
        ], $status);
    }

    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully',
    ): JsonResponse {
        return self::success($data, $message, HttpStatus::CREATED);
    }

    public static function deleted(
        string $message = 'Resource deleted successfully',
    ): JsonResponse {
        return self::success(new stdClass, $message, HttpStatus::OK);
    }

    public static function paginated(
        AnonymousResourceCollection $collection,
        string $message = 'Operation completed successfully',
    ): JsonResponse {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $collection->resource;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $collection->resolve(request()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], HttpStatus::OK);
    }

    public static function error(
        string $message,
        array $errors = [],
        int $status = HttpStatus::BAD_REQUEST,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => self::resolveErrors($errors),
        ], $status);
    }

    public static function validation(
        array $errors,
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return self::error($message, $errors, HttpStatus::UNPROCESSABLE_ENTITY);
    }

    private static function resolveData(mixed $data): mixed
    {
        if ($data === null) {
            return new stdClass;
        }

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->resolve(request());
        }

        return $data;
    }

    private static function resolveErrors(array $errors): stdClass|array
    {
        return $errors === [] ? new stdClass : $errors;
    }
}
