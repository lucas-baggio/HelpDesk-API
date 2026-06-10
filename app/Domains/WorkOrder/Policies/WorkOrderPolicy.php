<?php

namespace App\Domains\WorkOrder\Policies;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\Auth\Authenticatable;

class WorkOrderPolicy
{
    /**
     * RN-002: tecnico may execute and update work orders.
     * RN-003: atendente cannot finalize work orders or manage their lifecycle.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof User;
    }

    public function view(Authenticatable $user, WorkOrder $workOrder): bool
    {
        return $user instanceof User;
    }

    /** RN-022: only admin and tecnico may create work orders. */
    public function create(Authenticatable $user): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Tecnico], true);
    }

    /** RN-025: admin and tecnico may update description and service value. */
    public function update(Authenticatable $user, WorkOrder $workOrder): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Tecnico], true);
    }

    /** RN-023: status transitions restricted to admin and tecnico. */
    public function changeStatus(Authenticatable $user, WorkOrder $workOrder): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Tecnico], true);
    }
}
