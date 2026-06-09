<?php

namespace App\Domains\Ticket\Controllers;

use App\Domains\Ticket\Actions\CancelTicketAction;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Resources\TicketResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CancelTicketController extends ApiController
{
    public function __construct(private readonly CancelTicketAction $action) {}

    public function __invoke(Ticket $ticket): JsonResponse
    {
        $this->authorize('changeStatus', $ticket);

        $ticket = $this->action->execute($ticket);

        return ApiResponse::success(
            data: new TicketResource($ticket),
            message: 'Ticket cancelled successfully.',
        );
    }
}
