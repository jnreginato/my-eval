<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when evaluating expressions containing a 0^0.
 */
class ExponentialException extends MathParserException
{
    public function __construct()
    {
        parent::__construct('Zero raised to zero is undefined.');
    }
}
