<?php

namespace Tests\Unit\Domains\User\Actions;

use App\Domains\User\Actions\CreateUserAction;
use App\Domains\User\DTOs\CreateUserData;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_with_valid_data(): void
    {
        $data = new CreateUserData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'password123',
            role: UserRole::Tecnico,
        );

        $user = (new CreateUserAction)->execute($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => UserRole::Tecnico->value,
            'is_active' => true,
        ]);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_it_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('A user with this email already exists.');

        (new CreateUserAction)->execute(new CreateUserData(
            name: 'Jane Doe',
            email: 'existing@example.com',
            password: 'password123',
        ));
    }
}
