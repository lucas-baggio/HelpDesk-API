<?php

namespace App\Domains\Auth\Exceptions;

use App\Shared\Exceptions\ApiException;
use App\Shared\Http\HttpStatus;

class InvalidCredentialsException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::UNAUTHORIZED;
    }
}
