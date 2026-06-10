<?php

namespace App\Domains\WorkOrder\DTOs;

use App\Domains\WorkOrder\Requests\UpdateWorkOrderRequest;

readonly class UpdateWorkOrderData
{
    public function __construct(
        public ?string $description = null,
        public ?float $serviceValue = null,
    ) {}

    public static function fromRequest(UpdateWorkOrderRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            description: $validated['description'] ?? null,
            serviceValue: $validated['service_value'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        return array_filter(
            [
                'description' => $this->description,
                'service_value' => $this->serviceValue,
            ],
            fn (mixed $value): bool => $value !== null,
        );
    }
}
