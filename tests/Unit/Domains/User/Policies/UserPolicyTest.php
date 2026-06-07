<?php

namespace Tests\Unit\Domains\User\Policies;

use App\Domains\User\Models\User;
use App\Domains\User\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    public function test_user_cannot_view_any(): void
    {
        $user = $this->createMock(Authenticatable::class);
        $policy = new UserPolicy;

        $this->assertFalse($policy->viewAny($user));
    }

    public function test_user_cannot_view(): void
    {
        $user = $this->createMock(Authenticatable::class);
        $model = new User;
        $policy = new UserPolicy;

        $this->assertFalse($policy->view($user, $model));
    }

    public function test_user_cannot_create(): void
    {
        $user = $this->createMock(Authenticatable::class);
        $policy = new UserPolicy;

        $this->assertFalse($policy->create($user));
    }

    public function test_user_cannot_update(): void
    {
        $user = $this->createMock(Authenticatable::class);
        $model = new User;
        $policy = new UserPolicy;

        $this->assertFalse($policy->update($user, $model));
    }

    public function test_user_cannot_delete(): void
    {
        $user = $this->createMock(Authenticatable::class);
        $model = new User;
        $policy = new UserPolicy;

        $this->assertFalse($policy->delete($user, $model));
    }
}
