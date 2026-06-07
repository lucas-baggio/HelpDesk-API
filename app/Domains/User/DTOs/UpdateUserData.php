<?php

namespace App\Domains\User\DTOs;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Requests\UpdateUserRequest;

readonly class UpdateUserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?UserRole $role = null,
        public ?bool $isActive = null,
    ) {}

    public static function fromRequest(UpdateUserRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            password: $validated['password'] ?? null,
            role: isset($validated['role']) ? UserRole::from($validated['role']) : null,
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
            'role' => $this->role?->value,
            'is_active' => $this->isActive,
        ];

        return array_filter(
            $attributes,
            fn (mixed $value): bool => $value !== null,
        );
    }
}
