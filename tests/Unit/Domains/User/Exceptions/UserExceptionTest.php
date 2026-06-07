<?php

namespace Tests\Unit\Domains\User\Exceptions;

use App\Domains\User\Exceptions\UserException;
use Tests\TestCase;

class UserExceptionTest extends TestCase
{
    public function test_it_can_be_thrown_with_a_message(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Domain error');

        throw new UserException('Domain error');
    }
}
