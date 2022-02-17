<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing or evaluating expressions containing an unknown or undefined variable.
 */
class UnknownVariableException extends MathParserException
{
    /**
     * Create a UnknownVariableException.
     *
     * @param string $unknow
     */
    public function __construct(string $unknow)
    {
        parent::__construct("Unknown variable $unknow encountered.");

        $this->data = $unknow;
    }

    /**
     * Get the unknown variable.
     *
     * @return string
     */
    public function getVariable(): string
    {
        return $this->data;
    }
}
