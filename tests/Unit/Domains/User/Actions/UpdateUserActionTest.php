<?php

namespace Tests\Unit\Domains\User\Actions;

use App\Domains\User\Actions\UpdateUserAction;
use App\Domains\User\DTOs\UpdateUserData;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_provided_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => UserRole::Atendente->value,
            'is_active' => true,
        ]);

        $updated = (new UpdateUserAction)->execute($user, new UpdateUserData(
            name: 'Updated Name',
            role: UserRole::Admin,
            isActive: false,
        ));

        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame('original@example.com', $updated->email);
        $this->assertSame(UserRole::Admin, $updated->role);
        $this->assertFalse($updated->is_active);
    }

    public function test_it_updates_password_when_provided(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $updated = (new UpdateUserAction)->execute($user, new UpdateUserData(
            password: 'new-password123',
        ));

        $this->assertTrue(Hash::check('new-password123', $updated->password));
    }

    public function test_it_keeps_password_when_not_provided(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('same-password'),
        ]);

        $originalHash = $user->password;

        $updated = (new UpdateUserAction)->execute($user, new UpdateUserData(
            name: 'Updated Name',
        ));

        $this->assertSame($originalHash, $updated->password);
    }
}
