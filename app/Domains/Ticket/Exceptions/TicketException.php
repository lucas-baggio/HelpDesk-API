<?php

namespace App\Domains\Ticket\Exceptions;

use App\Shared\Exceptions\ApiException;
use App\Shared\Http\HttpStatus;

class TicketException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::UNPROCESSABLE_ENTITY;
    }
}
