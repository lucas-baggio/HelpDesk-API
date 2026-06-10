<?php

namespace App\Domains\History\Controllers;

use App\Domains\History\Actions\ListHistoriesAction;
use App\Domains\History\Models\History;
use App\Domains\History\Resources\HistoryResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListHistoriesController extends ApiController
{
    public function __construct(private readonly ListHistoriesAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', History::class);

        $histories = $this->action->execute(
            entityType: $request->query('entity_type') ?: null,
            entityId: $request->query('entity_id') ?: null,
            userId: $request->query('user_id') ?: null,
        );

        return ApiResponse::paginated(
            collection: HistoryResource::collection($histories),
            message: 'History retrieved successfully.',
        );
    }
}
