<?php

namespace Tests\Unit\Domains\User\Requests;

use App\Domains\User\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
{
    public function test_it_allows_partial_updates(): void
    {
        $validator = Validator::make(
            ['name' => 'Updated Name'],
            ['name' => (new UpdateUserRequest)->rules()['name']],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_it_validates_is_active_as_boolean(): void
    {
        $validator = Validator::make(
            ['is_active' => 'not-a-boolean'],
            ['is_active' => (new UpdateUserRequest)->rules()['is_active']],
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_active', $validator->errors()->toArray());
    }
}
