<?php

namespace App\Domains\User\Requests;

use App\Domains\User\DTOs\CreateUserData;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('role')) {
            $this->merge([
                'role' => UserRole::Atendente->value,
            ]);
        }
    }

    public function toCreateUserData(): CreateUserData
    {
        return CreateUserData::fromRequest($this);
    }
}
