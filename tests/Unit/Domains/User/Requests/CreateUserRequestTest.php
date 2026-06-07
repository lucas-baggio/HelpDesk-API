<?php

namespace Tests\Unit\Domains\User\Requests;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Requests\CreateUserRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateUserRequestTest extends TestCase
{
    public function test_it_requires_core_fields(): void
    {
        $validator = Validator::make([], (new CreateUserRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());
    }

    public function test_it_rejects_invalid_role(): void
    {
        $validator = Validator::make(
            ['role' => 'invalid-role'],
            ['role' => (new CreateUserRequest)->rules()['role']],
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());
    }

    public function test_it_accepts_valid_role_values(): void
    {
        foreach (UserRole::cases() as $role) {
            $validator = Validator::make(
                ['role' => $role->value],
                ['role' => (new CreateUserRequest)->rules()['role']],
            );

            $this->assertFalse($validator->fails(), "Role [{$role->value}] should be valid.");
        }
    }
}
