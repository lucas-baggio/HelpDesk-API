<?php

namespace App\Domains\User\Actions;

use App\Domains\User\DTOs\CreateUserData;
use App\Domains\User\Models\User;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class CreateUserAction
{
    public function execute(CreateUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            if (User::query()->where('email', $data->email)->exists()) {
                throw BusinessRuleException::withCode(
                    'USER_EMAIL_ALREADY_EXISTS',
                    'A user with this email already exists.',
                );
            }

            return User::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
                'role' => $data->role->value,
                'is_active' => true,
            ]);
        });
    }
}
