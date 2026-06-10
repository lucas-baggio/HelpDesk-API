<?php

namespace App\Domains\FileUpload\Policies;

use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class WorkOrderFilePolicy
{
    /**
     * RN-028: attachments belong to work orders — all roles may view them.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof User;
    }

    public function view(Authenticatable $user, WorkOrderFile $file): bool
    {
        return $user instanceof User;
    }

    /**
     * RN-028/RN-029: only admin and tecnico may attach files to work orders.
     */
    public function create(Authenticatable $user): bool
    {
        return $user instanceof User
            && in_array($user->role, [UserRole::Admin, UserRole::Tecnico], true);
    }

    /**
     * RN-027: deletion is restricted to admin and the original uploader.
     */
    public function delete(Authenticatable $user, WorkOrderFile $file): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->role === UserRole::Admin || $user->id === $file->uploaded_by;
    }
}
