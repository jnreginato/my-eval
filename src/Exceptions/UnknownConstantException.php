<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing or evaluating expressions containing an unknown constant.
 *
 * This should not happen under normal circumstances.
 */
class UnknownConstantException extends MathParserException
{
    /**
     * Create a UnknownConstantException.
     *
     * @param string $unknow
     */
    public function __construct(string $unknow)
    {
        parent::__construct("Unknown constant $unknow encountered.");

        $this->data = $unknow;
    }

    /**
     * Get the unknown constant that was encountered.
     *
     * @return string
     */
    public function getUnknownConstant(): string
    {
        return $this->data;
    }
}
