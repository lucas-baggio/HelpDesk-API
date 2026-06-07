<?php

namespace App\Domains\User\Controllers;

use App\Domains\User\Actions\UpdateUserAction;
use App\Domains\User\Models\User;
use App\Domains\User\Requests\UpdateUserRequest;
use App\Domains\User\Resources\UserResource;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateUserController extends ApiController
{
    public function __construct(
        private readonly UpdateUserAction $updateUserAction,
    ) {}

    public function __invoke(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->updateUserAction->execute(
            $user,
            $request->toUpdateUserData(),
        );

        return $this->success(
            new UserResource($updatedUser),
            'User updated successfully',
        );
    }
}
