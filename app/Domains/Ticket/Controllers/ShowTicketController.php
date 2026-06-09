<?php

namespace App\Domains\Ticket\Controllers;

use App\Domains\Ticket\Actions\ShowTicketAction;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Resources\TicketResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowTicketController extends ApiController
{
    public function __construct(private readonly ShowTicketAction $action) {}

    public function __invoke(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return ApiResponse::success(
            data: new TicketResource($this->action->execute($ticket)),
            message: 'Ticket retrieved successfully.',
        );
    }
}
