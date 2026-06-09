<?php

namespace App\Domains\Ticket\Policies;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class TicketPolicy
{
    /**
     * RN-018: only tecnico and admin may change technical statuses.
     * RN-003: atendente can create tickets.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof User;
    }

    public function view(Authenticatable $user, Ticket $ticket): bool
    {
        return $user instanceof User;
    }

    public function create(Authenticatable $user): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Atendente], true);
    }

    public function update(Authenticatable $user, Ticket $ticket): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Atendente], true);
    }

    /** RN-018: status transitions restricted to admin and tecnico. */
    public function changeStatus(Authenticatable $user, Ticket $ticket): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Tecnico], true);
    }

    public function delete(Authenticatable $user, Ticket $ticket): bool
    {
        return $user instanceof User && $user->role === UserRole::Admin;
    }
}
