<?php

declare(strict_types=1);

namespace MyEval\Lexing;

/**
 * Lexer capable of recognizing standard logic expressions.
 *
 * Subclass of the generic Lexer, with TokenDefinition patterns for numbers, elementary functions, arithmetic and
 * logical operations and variables.
 */
class PricingLexer extends Lexer
{
    public function __construct()
    {
        $this->add(new TokenDefinition('/\d+[,\.]\d+(e[+-]?\d+)?/', TokenType::REAL_NUMBER));

        $this->add(new TokenDefinition('/\d+/', TokenType::NATURAL_NUMBER));

        $this->add(new TokenDefinition('/\d*(\.\d\d)/', TokenType::STRING));

        $this->add(new TokenDefinition('/round/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/ceil/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/floor/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/ending/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/\(/', TokenType::OPEN_PARENTHESIS));
        $this->add(new TokenDefinition('/\)/', TokenType::CLOSE_PARENTHESIS));
        $this->add(new TokenDefinition('/\{/', TokenType::OPEN_BRACE));
        $this->add(new TokenDefinition('/\}/', TokenType::CLOSE_BRACE));

        $this->add(new TokenDefinition('/\+/', TokenType::ADDITION_OPERATOR));
        $this->add(new TokenDefinition('/\-/', TokenType::SUBTRACTION_OPERATOR));
        $this->add(new TokenDefinition('/\*/', TokenType::MULTIPLICATION_OPERATOR));
        $this->add(new TokenDefinition('/\//', TokenType::DIVISION_OPERATOR));
        $this->add(new TokenDefinition('/\^/', TokenType::EXPONENTIAL_OPERATOR));

        $this->add(new TokenDefinition('/\,/', TokenType::TERMINATOR));
        $this->add(new TokenDefinition('/\;/', TokenType::TERMINATOR));
        $this->add(new TokenDefinition('/\n/', TokenType::TERMINATOR));

        $this->add(new TokenDefinition('/\s+/', TokenType::WHITESPACE));

        $this->add(new TokenDefinition('/IF/', TokenType::IF));
        $this->add(new TokenDefinition('/if/', TokenType::IF));
        $this->add(new TokenDefinition('/THEN/', TokenType::THEN));
        $this->add(new TokenDefinition('/ELSE/', TokenType::ELSE));
        $this->add(new TokenDefinition('/else/', TokenType::ELSE));

        // Prefix operators
        $this->add(new TokenDefinition('/\!/', TokenType::NOT));
        $this->add(new TokenDefinition('/NOT/', TokenType::NOT));

        // Infix operators
        $this->add(new TokenDefinition('/\=/', TokenType::EQUAL_TO, '='));
        $this->add(new TokenDefinition('/\<\>/', TokenType::DIFFERENT_THAN, '<>'));
        $this->add(new TokenDefinition('/\>\=/', TokenType::GREATER_OR_EQUAL_THAN, '>='));
        $this->add(new TokenDefinition('/\<\=/', TokenType::LESS_OR_EQUAL_THAN, '<='));
        $this->add(new TokenDefinition('/\>/', TokenType::GREATER_THAN, '>'));
        $this->add(new TokenDefinition('/\</', TokenType::LESS_THAN, '<'));

        $this->add(new TokenDefinition('/\&\&/', TokenType::AND));
        $this->add(new TokenDefinition('/\|\|/', TokenType::OR));
        $this->add(new TokenDefinition('/AND/', TokenType::AND));
        $this->add(new TokenDefinition('/OR/', TokenType::OR));

        // Operands
        $this->add(new TokenDefinition('/TRUE/', TokenType::BOOLEAN));
        $this->add(new TokenDefinition('/true/', TokenType::BOOLEAN));
        $this->add(new TokenDefinition('/FALSE/', TokenType::BOOLEAN));
        $this->add(new TokenDefinition('/false/', TokenType::BOOLEAN));

        $this->add(new TokenDefinition('/[a-zA-Z]+/', TokenType::VARIABLE));
    }
}
