<?php

namespace App\Domains\Client\DTOs;

use App\Domains\Client\Requests\CreateClientRequest;

readonly class CreateClientData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $cpf_cnpj,
        public string $phone,
        public string $street,
        public string $number,
        public string $state,
        public string $district,
        public string $city,
        public string $zip_code,
        public ?string $complement = null,
    ) {}

    public static function fromRequest(CreateClientRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            cpf_cnpj: $request->validated('cpf_cnpj'),
            phone: $request->validated('phone'),
            street: $request->validated('street'),
            number: $request->validated('number'),
            state: $request->validated('state'),
            district: $request->validated('district'),
            city: $request->validated('city'),
            zip_code: $request->validated('zip_code'),
            complement: $request->validated('complement'),
        );
    }
}
