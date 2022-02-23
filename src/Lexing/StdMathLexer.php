<?php

declare(strict_types=1);

namespace MyEval\Lexing;

/**
 * Lexer capable of recognizing all standard mathematical expressions.
 *
 * Subclass of the generic Lexer, with TokenDefinition patterns for numbers, elementary functions, arithmetic
 * operations and variables.
 *
 * ## Recognized tokens
 *
 * All commom tokens plus:
 *
 *  `/\!/\!/` matching !! for semi-factorial
 *  `/\!/` matching ! for factorial
 *
 *  `/\NAN/` matching for a not a number
 *  `/\INF/` matching for infinite
 *
 *  `/e/` matching constant e
 *  `/pi/` matching constant pi
 *
 *  `/[a-zA-Z]/` matching variables*
 *  * note that we only allow single letter identifiers, this improves parsing of implicit multiplication.
 */
class StdMathLexer extends AbstractLexer
{
    public function __construct()
    {
        parent::__construct();

        // Postfix operators
        $this->add(new TokenDefinition('/\!\!/', TokenType::SEMI_FACTORIAL_OPERATOR));
        $this->add(new TokenDefinition('/\!/', TokenType::FACTORIAL_OPERATOR));

        $this->add(new TokenDefinition('/NAN/', TokenType::CONSTANT));
        $this->add(new TokenDefinition('/INF/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/e/', TokenType::CONSTANT));
        $this->add(new TokenDefinition('/pi/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/[a-zA-Z]/', TokenType::VARIABLE));
    }
}
