<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing or evaluating expressions containing an unknown function symbol.
 *
 * This should not happen under normal circumstances.
 */
class UnknownFunctionException extends MathParserException
{
    /**
     * Create a UnknownFunctionException.
     *
     * @param string $unknow
     */
    public function __construct(string $unknow)
    {
        parent::__construct("Unknown function $unknow encountered.");

        $this->data = $unknow;
    }

    /**
     * Get the unknown function that was encountered.
     *
     * @return string
     */
    public function getUnknownFunction(): string
    {
        return $this->data;
    }
}
