<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing or evaluating expressions containing an unknown operator.
 *
 * This should not happen under normal circumstances.
 */
class UnknownOperatorException extends MathParserException
{
    /**
     * Create a UnknownOperatorException.
     *
     * @param string $unknow
     */
    public function __construct(string $unknow)
    {
        parent::__construct("Unknown operator $unknow encountered.");

        $this->data = $unknow;
    }

    /**
     * Get the unknown operator that was encountered.
     *
     * @return string
     */
    public function getUnknownOperator(): string
    {
        return $this->data;
    }
}
