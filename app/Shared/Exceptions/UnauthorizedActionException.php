<?php

namespace App\Shared\Exceptions;

use App\Shared\Http\HttpStatus;

class UnauthorizedActionException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::FORBIDDEN;
    }
}
