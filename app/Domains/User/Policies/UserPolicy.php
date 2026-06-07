<?php

namespace App\Domains\User\Policies;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserPolicy
{
    /**
     * RN-001: user management is restricted to admin.
     * Enforcement starts when Auth middleware is wired to the API.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(Authenticatable $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(Authenticatable $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(Authenticatable $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(Authenticatable $user): bool
    {
        return $user instanceof User && $user->role === UserRole::Admin;
    }
}
