<?php

namespace App\Domains\Auth\Actions;

use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Auth;

class GetAuthenticatedUserAction
{
    public function execute(): User
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        return $user;
    }
}
