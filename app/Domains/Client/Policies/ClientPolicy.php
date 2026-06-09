<?php

namespace App\Domains\Client\Policies;

use App\Domains\Client\Models\Client;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class ClientPolicy
{
    /**
     * RN-003: atendente can register and edit clients.
     * RN-001: admin has full access.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $this->isAdminOrAtendente($user);
    }

    public function view(Authenticatable $user, Client $client): bool
    {
        return $this->isAdminOrAtendente($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdminOrAtendente($user);
    }

    public function update(Authenticatable $user, Client $client): bool
    {
        return $this->isAdminOrAtendente($user);
    }

    public function delete(Authenticatable $user, Client $client): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(Authenticatable $user): bool
    {
        return $user instanceof User && $user->role === UserRole::Admin;
    }

    private function isAdminOrAtendente(Authenticatable $user): bool
    {
        return $user instanceof User && in_array($user->role, [UserRole::Admin, UserRole::Atendente], true);
    }
}
