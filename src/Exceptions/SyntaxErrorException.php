<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

/**
 * Exception thrown when parsing expressions that are not well-formed.
 */
class SyntaxErrorException extends MathParserException
{
    public function __construct()
    {
        parent::__construct('Syntax error.');
    }
}
