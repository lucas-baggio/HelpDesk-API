<?php

namespace Tests\Unit\Domains\User\DTOs;

use App\Domains\User\DTOs\CreateUserData;
use App\Domains\User\DTOs\UpdateUserData;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Requests\CreateUserRequest;
use App\Domains\User\Requests\UpdateUserRequest;
use Mockery;
use Tests\TestCase;

class UserDataTest extends TestCase
{
    public function test_create_user_data_is_built_from_validated_request(): void
    {
        $request = Mockery::mock(CreateUserRequest::class);
        $request->shouldReceive('validated')->with('name')->andReturn('Jane Doe');
        $request->shouldReceive('validated')->with('email')->andReturn('jane@example.com');
        $request->shouldReceive('validated')->with('password')->andReturn('password123');
        $request->shouldReceive('validated')->with('role')->andReturn(UserRole::Admin->value);

        $data = CreateUserData::fromRequest($request);

        $this->assertSame('Jane Doe', $data->name);
        $this->assertSame('jane@example.com', $data->email);
        $this->assertSame('password123', $data->password);
        $this->assertSame(UserRole::Admin, $data->role);
    }

    public function test_create_user_request_exposes_dto_factory(): void
    {
        $request = Mockery::mock(CreateUserRequest::class)->makePartial();
        $request->shouldReceive('validated')->with('name')->andReturn('Jane Doe');
        $request->shouldReceive('validated')->with('email')->andReturn('jane@example.com');
        $request->shouldReceive('validated')->with('password')->andReturn('password123');
        $request->shouldReceive('validated')->with('role')->andReturn(UserRole::Atendente->value);

        $data = $request->toCreateUserData();

        $this->assertInstanceOf(CreateUserData::class, $data);
        $this->assertSame(UserRole::Atendente, $data->role);
    }

    public function test_update_user_data_maps_only_provided_fields(): void
    {
        $request = Mockery::mock(UpdateUserRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'name' => 'Updated Name',
            'is_active' => false,
        ]);

        $data = UpdateUserData::fromRequest($request);

        $this->assertSame('Updated Name', $data->name);
        $this->assertNull($data->email);
        $this->assertNull($data->password);
        $this->assertNull($data->role);
        $this->assertFalse($data->isActive);
        $this->assertSame([
            'name' => 'Updated Name',
            'is_active' => false,
        ], $data->toPersistenceArray());
    }

    public function test_update_user_data_excludes_password_from_persistence_array(): void
    {
        $data = new UpdateUserData(password: 'new-password123');

        $this->assertSame([], $data->toPersistenceArray());
    }
}
