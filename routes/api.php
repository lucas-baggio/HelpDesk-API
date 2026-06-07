<?php

use App\Shared\Http\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ApiResponse::success(
        data: ['status' => 'ok'],
        message: 'API is running',
    );
});
