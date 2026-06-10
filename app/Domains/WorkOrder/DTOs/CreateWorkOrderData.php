<?php

namespace App\Domains\WorkOrder\DTOs;

use App\Domains\WorkOrder\Requests\CreateWorkOrderRequest;

readonly class CreateWorkOrderData
{
    public function __construct(
        public string $ticketId,
        public string $description,
        public ?float $serviceValue = null,
    ) {}

    public static function fromRequest(CreateWorkOrderRequest $request): self
    {
        return new self(
            ticketId: $request->validated('ticket_id'),
            description: $request->validated('description'),
            serviceValue: $request->validated('service_value'),
        );
    }
}
