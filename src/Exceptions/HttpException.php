<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Exceptions;

use Throwable;

class HttpException extends EsewaException
{
    /**
     * The HTTP status code.
     */
    private int $statusCode;

    public function __construct(string $message, int $statusCode, ?Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int The status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
