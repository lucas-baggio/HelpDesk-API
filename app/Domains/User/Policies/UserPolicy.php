<?php

namespace App\Domains\User\Policies;

use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return false;
    }

    public function view(Authenticatable $user, User $user): bool
    {
        return false;
    }

    public function create(Authenticatable $user): bool
    {
        return false;
    }

    public function update(Authenticatable $user, User $user): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, User $user): bool
    {
        return false;
    }
}
