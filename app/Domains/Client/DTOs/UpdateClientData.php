<?php

namespace App\Domains\Client\DTOs;

use App\Domains\Client\Requests\UpdateClientRequest;

readonly class UpdateClientData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $cpf_cnpj = null,
        public ?string $phone = null,
        public ?string $street = null,
        public ?string $number = null,
        public ?string $state = null,
        public ?string $district = null,
        public ?string $city = null,
        public ?string $zip_code = null,
        public ?string $complement = null,
        public ?bool $isActive = null,
    ) {}

    public static function fromRequest(UpdateClientRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            cpf_cnpj: $validated['cpf_cnpj'] ?? null,
            phone: $validated['phone'] ?? null,
            street: $validated['street'] ?? null,
            number: $validated['number'] ?? null,
            state: $validated['state'] ?? null,
            district: $validated['district'] ?? null,
            city: $validated['city'] ?? null,
            zip_code: $validated['zip_code'] ?? null,
            complement: $validated['complement'] ?? null,
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
            'email' => $this->email,
            'cpf_cnpj' => $this->cpf_cnpj,
            'phone' => $this->phone,
            'street' => $this->street,
            'number' => $this->number,
            'state' => $this->state,
            'district' => $this->district,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
            'complement' => $this->complement,
            'is_active' => $this->isActive,
        ];

        return array_filter(
            $attributes,
            fn (mixed $value): bool => $value !== null,
        );
    }
}
