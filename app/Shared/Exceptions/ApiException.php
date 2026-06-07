<?php

namespace App\Shared\Exceptions;

use Exception;
use Throwable;

abstract class ApiException extends Exception
{
    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        string $message = '',
        protected array $errors = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    abstract public function statusCode(): int;

    /**
     * @return array<string, mixed>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
