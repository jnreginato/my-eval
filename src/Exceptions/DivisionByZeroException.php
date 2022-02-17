<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when evaluating expressions containing a division by zero.
 */
class DivisionByZeroException extends MathParserException
{
    public function __construct()
    {
        parent::__construct('Division by zero.');
    }
}
