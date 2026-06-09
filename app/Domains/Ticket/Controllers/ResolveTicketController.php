<?php

namespace App\Domains\Ticket\Controllers;

use App\Domains\Ticket\Actions\ResolveTicketAction;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Resources\TicketResource;
use App\Domains\User\Models\User;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ResolveTicketController extends ApiController
{
    public function __construct(private readonly ResolveTicketAction $action) {}

    public function __invoke(Ticket $ticket): JsonResponse
    {
        $this->authorize('changeStatus', $ticket);

        /** @var User $user */
        $user = auth()->user();

        $ticket = $this->action->execute($ticket, $user);

        return ApiResponse::success(
            data: new TicketResource($ticket),
            message: 'Ticket resolved successfully.',
        );
    }
}
