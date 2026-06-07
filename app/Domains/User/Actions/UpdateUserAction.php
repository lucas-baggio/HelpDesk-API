<?php

namespace App\Domains\User\Actions;

use App\Domains\User\DTOs\UpdateUserData;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    public function execute(User $user, UpdateUserData $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->fill($data->toPersistenceArray());

            if ($data->password !== null) {
                $user->password = $data->password;
            }

            $user->save();

            return $user->fresh();
        });
    }
}
