<?php

namespace App\Domains\Ticket\DTOs;

use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Requests\UpdateTicketRequest;

readonly class UpdateTicketData
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?TicketPriority $priority = null,
        public ?string $machineId = null,
    ) {}

    public static function fromRequest(UpdateTicketRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            title: $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            priority: isset($validated['priority'])
                ? TicketPriority::from($validated['priority'])
                : null,
            machineId: array_key_exists('machine_id', $validated)
                ? $validated['machine_id']
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        $attributes = [
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority?->value,
            'machine_id' => $this->machineId,
        ];

        return array_filter(
            $attributes,
            fn (mixed $value): bool => $value !== null,
        );
    }
}
