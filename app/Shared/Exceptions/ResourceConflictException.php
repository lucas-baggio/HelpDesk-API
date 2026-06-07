<?php

namespace App\Shared\Exceptions;

use App\Shared\Http\HttpStatus;

class ResourceConflictException extends ApiException
{
    public function statusCode(): int
    {
        return HttpStatus::CONFLICT;
    }

    public static function withCode(string $code, string $message): self
    {
        return new self($message, ['code' => $code]);
    }
}
