<?php

namespace App\Shared\Exceptions;

use App\Shared\Http\HttpStatus;

class BusinessRuleException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::UNPROCESSABLE_ENTITY;
    }

    public static function withCode(string $code, string $message): self
    {
        return new self($message, ['code' => $code]);
    }
}
