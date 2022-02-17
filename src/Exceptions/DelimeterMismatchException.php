<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing expressions having non-matching left and right parentheses.
 */
class DelimeterMismatchException extends MathParserException
{
    /**
     * Create a DelimeterMismatchException.
     *
     * @param string $data
     */
    public function __construct(string $data = '')
    {
        parent::__construct('Unable to match delimiters.');

        $this->data = $data;
    }
}
