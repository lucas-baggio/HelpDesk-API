<?php

namespace App\Domains\Ticket\Controllers;

use App\Domains\Ticket\Actions\ListTicketsAction;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Resources\TicketResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTicketsController extends ApiController
{
    public function __construct(private readonly ListTicketsAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->action->execute(
            clientId: $request->query('client_id') ?: null,
            status: TicketStatus::tryFrom($request->query('status', '')),
            priority: TicketPriority::tryFrom($request->query('priority', '')),
        );

        return ApiResponse::paginated(
            collection: TicketResource::collection($tickets),
            message: 'Tickets retrieved successfully.',
        );
    }
}
