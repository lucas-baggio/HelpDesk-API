<?php

namespace App\Domains\Machine\Policies;

use App\Domains\Machine\Models\Machine;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class MachinePolicy
{
    /**
     * RN-001: admin has full access.
     * RN-003: atendente can manage client-linked data.
     * RN-002: tecnico can view machines for technical work.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof User;
    }

    public function view(Authenticatable $user, Machine $machine): bool
    {
        return $user instanceof User;
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdminOrAtendente($user);
    }

    public function update(Authenticatable $user, Machine $machine): bool
    {
        return $this->isAdminOrAtendente($user);
    }

    public function delete(Authenticatable $user, Machine $machine): bool
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
