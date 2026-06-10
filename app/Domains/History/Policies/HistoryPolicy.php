<?php

namespace App\Domains\History\Policies;

use App\Domains\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class HistoryPolicy
{
    /** History is read-only from the API — all authenticated roles may view. */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof User;
    }

    public function view(Authenticatable $user): bool
    {
        return $user instanceof User;
    }
}
