<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\LoginAction;
use App\Domains\Auth\DTOs\LoginData;
use App\Domains\Auth\Exceptions\InactiveUserException;
use App\Domains\Auth\Exceptions\InvalidCredentialsException;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = (new LoginAction)->execute(new LoginData(
            email: 'user@example.com',
            password: 'password123',
        ));

        $this->assertNotEmpty($result['access_token']);
        $this->assertSame('bearer', $result['token_type']);
        $this->assertSame('user@example.com', $result['user']->email);
    }

    public function test_it_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(InvalidCredentialsException::class);

        (new LoginAction)->execute(new LoginData(
            email: 'user@example.com',
            password: 'wrong-password',
        ));
    }

    public function test_it_rejects_inactive_users(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $this->expectException(InactiveUserException::class);

        (new LoginAction)->execute(new LoginData(
            email: 'inactive@example.com',
            password: 'password123',
        ));
    }
}
