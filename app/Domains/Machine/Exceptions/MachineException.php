<?php

namespace App\Domains\Machine\Exceptions;

use App\Shared\Exceptions\ApiException;
use App\Shared\Http\HttpStatus;

class MachineException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::UNPROCESSABLE_ENTITY;
    }
}
