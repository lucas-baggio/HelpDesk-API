<?php

namespace App\Domains\User\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
