<?php

use App\Domains\User\Controllers\CreateUserController;
use App\Domains\User\Controllers\UpdateUserController;
use App\Shared\Http\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ApiResponse::success(
        data: ['status' => 'ok'],
        message: 'API is running',
    );
});

// TODO: wrap in auth middleware when Auth domain is implemented.
Route::post('/users', CreateUserController::class);
Route::put('/users/{user}', UpdateUserController::class);
