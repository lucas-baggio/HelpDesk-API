<?php

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\LoginData;
use App\Domains\Auth\Exceptions\InactiveUserException;
use App\Domains\Auth\Exceptions\InvalidCredentialsException;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginAction
{
    /**
     * @return array{access_token: string, token_type: string, expires_in: int, user: User}
     */
    public function execute(LoginData $data): array
    {
        $token = Auth::guard('api')->attempt([
            'email' => $data->email,
            'password' => $data->password,
        ]);

        if (! $token) {
            throw new InvalidCredentialsException('Invalid email or password.');
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();

        if (! $user->is_active) {
            Auth::guard('api')->logout();

            throw new InactiveUserException('This user account is inactive.');
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user,
        ];
    }
}
