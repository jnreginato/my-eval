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
 *  `/e/` matching constant e
 *
 *  `/[a-zA-Z]/` matching variables*
 *  * note that we only allow single letter identifiers, this improves parsing of implicit multiplication.
 */
class StdMathLexer extends AbstractLexer
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new TokenDefinition('/e/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/[a-zA-Z]/', TokenType::VARIABLE));
    }
}
