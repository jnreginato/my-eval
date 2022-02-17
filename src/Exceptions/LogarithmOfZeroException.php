<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when evaluating expressions containing a log(0).
 */
class LogarithmOfZeroException extends MathParserException
{
    public function __construct()
    {
        parent::__construct('Logarithm of zero is undefined.');
    }
}
