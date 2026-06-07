<?php

namespace Tests\Unit\Domains\User\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_it_is_an_eloquent_model(): void
    {
        $model = new User;

        $this->assertInstanceOf(Model::class, $model);
    }
}
