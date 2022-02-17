<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing or evaluating expressions containing an unexpected operator.
 *
 * This should not happen under normal circumstances.
 */
class UnexpectedOperatorException extends MathParserException
{
    /**
     * Create an UnexpectedOperatorException.
     *
     * @param string $passed
     * @param string $expected
     */
    public function __construct(string $passed, string $expected)
    {
        parent::__construct("Unexpected operator $passed passed, expected $expected");

        $this->data = $passed;
    }

    /**
     * Get the unexpected operator that was encountered.
     *
     * @return string
     */
    public function getUnexpectedOperator(): string
    {
        return $this->data;
    }
}
