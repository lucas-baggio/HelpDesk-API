<?php

use App\Domains\Auth\Controllers\LoginController;
use App\Domains\Auth\Controllers\MeController;
use App\Domains\Client\Controllers\CreateClientController;
use App\Domains\Client\Controllers\DeactivateClientController;
use App\Domains\Client\Controllers\ListClientsController;
use App\Domains\Client\Controllers\ShowClientController;
use App\Domains\Client\Controllers\UpdateClientController;
use App\Domains\Machine\Controllers\CreateMachineController;
use App\Domains\Machine\Controllers\DeactivateMachineController;
use App\Domains\Machine\Controllers\ListMachinesController;
use App\Domains\Machine\Controllers\ShowMachineController;
use App\Domains\Machine\Controllers\UpdateMachineController;
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

Route::prefix('auth')->group(function (): void {
    Route::post('/login', LoginController::class);

    Route::middleware('auth:api')->group(function (): void {
        Route::get('/me', MeController::class);
    });
});

Route::middleware('auth:api')->group(function (): void {
    Route::post('/users', CreateUserController::class);
    Route::put('/users/{user}', UpdateUserController::class);

    Route::get('/clients', ListClientsController::class);
    Route::post('/clients', CreateClientController::class);
    Route::get('/clients/{client}', ShowClientController::class);
    Route::put('/clients/{client}', UpdateClientController::class);
    Route::delete('/clients/{client}', DeactivateClientController::class);

    Route::get('/machines', ListMachinesController::class);
    Route::post('/machines', CreateMachineController::class);
    Route::get('/machines/{machine}', ShowMachineController::class);
    Route::put('/machines/{machine}', UpdateMachineController::class);
    Route::delete('/machines/{machine}', DeactivateMachineController::class);
});
