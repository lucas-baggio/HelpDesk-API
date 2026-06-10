<?php

namespace App\Domains\History\Controllers;

use App\Domains\History\Models\History;
use App\Domains\History\Resources\HistoryResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowHistoryController extends ApiController
{
    public function __invoke(History $history): JsonResponse
    {
        $this->authorize('view', $history);

        return ApiResponse::success(
            data: new HistoryResource($history),
            message: 'History entry retrieved successfully.',
        );
    }
}
