<?php

namespace App\Domains\Ticket\DTOs;

use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Requests\CreateTicketRequest;

readonly class CreateTicketData
{
    public function __construct(
        public string $clientId,
        public string $createdBy,
        public string $title,
        public string $description,
        public TicketPriority $priority,
        public ?string $machineId = null,
    ) {}

    public static function fromRequest(CreateTicketRequest $request): self
    {
        return new self(
            clientId: $request->validated('client_id'),
            createdBy: $request->user()->id,
            title: $request->validated('title'),
            description: $request->validated('description'),
            priority: TicketPriority::from($request->validated('priority')),
            machineId: $request->validated('machine_id'),
        );
    }
}
