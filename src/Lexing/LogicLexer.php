<?php

declare(strict_types=1);

namespace MyEval\Lexing;

/**
 * Lexer capable of recognizing all standard logic expressions.
 *
 * Subclass of the generic Lexer, with TokenDefinition patterns for numbers, elementary functions, arithmetic
 * operations and variables.
 *
 * ## Recognized tokens
 *
 * All commom tokens plus:
 *
 *  `/IF/` or `/if/` matching a ternary condition IF operation
 *  `/THEN/` or `/then/` matching a ternary condition THEN operation
 *  `/ELSE/` or `/else/` matching a ternary condition ELSE operation
 *
 *  `/\=/` matching equal
 *  `/\<\>/` matching differente than
 *  `/\>\=/` matching greater or equal than
 *  `/\>\=/` matching less or equal than
 *  `/\>/` matching greater than
 *  `/\>/` matching less than
 *
 *  `/\&\&/` or `/AND/` matching AND
 *  `/\|\|/` or `/OR/` matching OR
 *
 *  `/TRUE/` or `/true/` matching TRUE
 *  `/FALSE/` or `/false/` matching FALSE
 *
 *  `/e/` matching constant e
 *
 *  `/[a-zA-Z]+/` matching variables of any number of letters
 */
class LogicLexer extends AbstractLexer
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new TokenDefinition('/IF/', TokenType::IF));
        $this->add(new TokenDefinition('/if/', TokenType::IF));
        $this->add(new TokenDefinition('/THEN/', TokenType::THEN));
        $this->add(new TokenDefinition('/ELSE/', TokenType::ELSE));
        $this->add(new TokenDefinition('/else/', TokenType::ELSE));

        // Prefix operators
        // $this->add(new TokenDefinition('/\!/', TokenType::NOT));
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

        $this->add(new TokenDefinition('/e/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/[a-zA-Z]+/', TokenType::VARIABLE));
    }
}
