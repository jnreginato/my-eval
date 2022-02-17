<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when a null operand found in an expression.
 */
class NullOperandException extends MathParserException
{
    public function __construct()
    {
        parent::__construct('Null operand found.');
    }
}
