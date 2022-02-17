<?php

declare(strict_types=1);

namespace MyEval\Lexing;

/**
 * Lexer capable of recognizing complex number mathematical expressions.
 *
 * Subclass of the generic Lexer, with TokenDefinition patterns for numbers, elementary functions, arithmetic
 * operations and variables.
 *
 * ## Recognized tokens
 *
 * All commom tokens plus:
 *
 *  `/arg/' matching (principal) argument
 *  `/conj/` matching conjugate
 *  `/re/` matching real part
 *  `/im/` matching imaginary part
 *
 *  `/i/` matching imaginary unit i
 *  `/e/` matching constant e
 *
 *  `/[a-zA-Z]/` matching variables*
 * * note that we only allow single letter identifiers, this improves parsing of implicit multiplication.
 */
class ComplexMathLexer extends AbstractLexer
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new TokenDefinition('/arg/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/conj/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/re/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/im/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/i/', TokenType::CONSTANT));
        $this->add(new TokenDefinition('/e/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/[a-zA-Z]/', TokenType::VARIABLE));
    }
}
