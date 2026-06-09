<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Actions\GetAuthenticatedUserAction;
use App\Domains\User\Resources\UserResource;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class MeController extends ApiController
{
    public function __construct(
        private readonly GetAuthenticatedUserAction $getAuthenticatedUserAction,
    ) {}

    public function __invoke(): JsonResponse
    {
        return $this->success(
            new UserResource($this->getAuthenticatedUserAction->execute()),
            'Authenticated user retrieved successfully',
        );
    }
}
