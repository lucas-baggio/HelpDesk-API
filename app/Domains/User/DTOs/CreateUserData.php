<?php

namespace App\Domains\User\DTOs;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Requests\CreateUserRequest;

readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role = UserRole::Atendente,
    ) {}

    public static function fromRequest(CreateUserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            role: UserRole::from($request->validated('role')),
        );
    }
}
