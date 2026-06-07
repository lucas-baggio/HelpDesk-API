<?php

namespace App\Shared\Exceptions;

use App\Shared\Http\ApiResponse;
use App\Shared\Http\HttpStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::validation(
                $exception->errors(),
                $exception->getMessage(),
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                $exception->getMessage() ?: 'Unauthenticated.',
                status: HttpStatus::UNAUTHORIZED,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                $exception->getMessage() ?: 'This action is unauthorized.',
                status: HttpStatus::FORBIDDEN,
            );
        });

        $exceptions->render(function (ApiException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                $exception->getMessage() ?: 'Operation failed.',
                $exception->errors(),
                $exception->statusCode(),
            );
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                'Resource not found.',
                status: HttpStatus::NOT_FOUND,
            );
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if (! $request->is('api/*') || $exception instanceof NotFoundHttpException) {
                return null;
            }

            return ApiResponse::error(
                $exception->getMessage() ?: 'Operation failed.',
                status: $exception->getStatusCode(),
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*') || config('app.debug')) {
                return null;
            }

            report($exception);

            return ApiResponse::error(
                'Internal server error.',
                status: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        });
    }
}
