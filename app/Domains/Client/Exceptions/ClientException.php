<?php

namespace App\Domains\Client\Exceptions;

use App\Shared\Exceptions\ApiException;
use App\Shared\Http\HttpStatus;

class ClientException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::UNPROCESSABLE_ENTITY;
    }
}
