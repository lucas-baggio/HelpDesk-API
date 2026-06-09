<?php

namespace App\Domains\Machine\DTOs;

use App\Domains\Machine\Requests\UpdateMachineRequest;

readonly class UpdateMachineData
{
    public function __construct(
        public ?string $name = null,
        public ?string $model = null,
        public ?string $serialNumber = null,
        public ?bool $isActive = null,
    ) {}

    public static function fromRequest(UpdateMachineRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'] ?? null,
            model: $validated['model'] ?? null,
            serialNumber: $validated['serial_number'] ?? null,
            isActive: array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        $attributes = [
            'name' => $this->name,
            'model' => $this->model,
            'serial_number' => $this->serialNumber,
            'is_active' => $this->isActive,
        ];

        return array_filter(
            $attributes,
            fn (mixed $value): bool => $value !== null,
        );
    }
}
