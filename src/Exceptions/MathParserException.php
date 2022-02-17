<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use Exception;

/**
 * Base class for the exceptions thrown by the library.
 */
abstract class MathParserException extends Exception
{
    /**
     * @var string Additional information about the exception.
     */
    protected string $data = '';

    /**
     * Get additional information about the exception.
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
}
