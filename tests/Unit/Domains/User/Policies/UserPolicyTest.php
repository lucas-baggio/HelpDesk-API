<?php

namespace Tests\Unit\Domains\User\Policies;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Domains\User\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $target));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $target));
        $this->assertTrue($policy->delete($admin, $target));
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $atendente = User::factory()->create([
            'role' => UserRole::Atendente->value,
        ]);
        $target = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertFalse($policy->viewAny($atendente));
        $this->assertFalse($policy->view($atendente, $target));
        $this->assertFalse($policy->create($atendente));
        $this->assertFalse($policy->update($atendente, $target));
        $this->assertFalse($policy->delete($atendente, $target));
    }

    public function test_non_user_authenticatable_cannot_manage_users(): void
    {
        $authenticatable = $this->createMock(Authenticatable::class);
        $target = new User;
        $policy = new UserPolicy;

        $this->assertFalse($policy->create($authenticatable));
        $this->assertFalse($policy->update($authenticatable, $target));
    }
}
