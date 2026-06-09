<?php

namespace App\Domains\Ticket\Controllers;

use App\Domains\Ticket\Actions\UpdateTicketAction;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Requests\UpdateTicketRequest;
use App\Domains\Ticket\Resources\TicketResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateTicketController extends ApiController
{
    public function __construct(private readonly UpdateTicketAction $action) {}

    public function __invoke(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->action->execute($ticket, $request->toUpdateTicketData());

        return ApiResponse::success(
            data: new TicketResource($ticket),
            message: 'Ticket updated successfully.',
        );
    }
}
