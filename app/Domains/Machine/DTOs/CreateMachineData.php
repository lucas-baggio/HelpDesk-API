<?php

namespace App\Domains\Machine\DTOs;

use App\Domains\Machine\Requests\CreateMachineRequest;

readonly class CreateMachineData
{
    public function __construct(
        public string $clientId,
        public string $name,
        public ?string $model = null,
        public ?string $serialNumber = null,
    ) {}

    public static function fromRequest(CreateMachineRequest $request): self
    {
        return new self(
            clientId: $request->validated('client_id'),
            name: $request->validated('name'),
            model: $request->validated('model'),
            serialNumber: $request->validated('serial_number'),
        );
    }
}
