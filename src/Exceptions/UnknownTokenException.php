<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when tokenizing expressions containing illegal characters.
 */
class UnknownTokenException extends MathParserException
{
    /**
     * Create a UnknownTokenException.
     *
     * @param string $unknow
     */
    public function __construct(string $unknow)
    {
        parent::__construct("Unknown token $unknow encountered.");

        $this->data = $unknow;
    }

    /**
     * Get the unknown token that was encountered.
     *
     * @return string
     */
    public function getUnknownToken(): string
    {
        return $this->data;
    }
}
