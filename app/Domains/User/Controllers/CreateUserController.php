<?php

namespace App\Domains\User\Controllers;

use App\Domains\User\Actions\CreateUserAction;
use App\Domains\User\Requests\CreateUserRequest;
use App\Domains\User\Resources\UserResource;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateUserController extends ApiController
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
    ) {}

    public function __invoke(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createUserAction->execute($request->toCreateUserData());

        return $this->created(
            new UserResource($user),
            'User created successfully',
        );
    }
}
