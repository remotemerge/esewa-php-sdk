<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Exceptions;

use Exception;
use Throwable;

final class EsewaException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
