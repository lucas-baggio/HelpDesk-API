<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Actions\LoginAction;
use App\Domains\Auth\Requests\LoginRequest;
use App\Domains\User\Resources\UserResource;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class LoginController extends ApiController
{
    public function __construct(
        private readonly LoginAction $loginAction,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->loginAction->execute($request->toLoginData());

        return $this->success(
            data: [
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'user' => new UserResource($result['user']),
            ],
            message: 'Login successful',
        );
    }
}
