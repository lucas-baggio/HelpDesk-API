<?php

namespace App\Domains\Ticket\Controllers;

use App\Domains\Ticket\Actions\CreateTicketAction;
use App\Domains\Ticket\Requests\CreateTicketRequest;
use App\Domains\Ticket\Resources\TicketResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateTicketController extends ApiController
{
    public function __construct(private readonly CreateTicketAction $action) {}

    public function __invoke(CreateTicketRequest $request): JsonResponse
    {
        $ticket = $this->action->execute($request->toCreateTicketData());

        return ApiResponse::created(
            data: new TicketResource($ticket),
            message: 'Ticket created successfully.',
        );
    }
}
