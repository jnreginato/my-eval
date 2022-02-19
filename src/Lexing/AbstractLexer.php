<?php

declare(strict_types=1);

namespace MyEval\Lexing;

/**
 * Lexer capable of recognizing all standard commom mathematical expressions.
 *
 * Subclass of the generic Lexer, with TokenDefinition patterns for numbers, elementary functions, arithmetic
 * operations and variables.
 *
 * ## Recognized tokens:
 *
 *  `/\d+[,\.]\d+(e[+-]?\d+)?/` matching real numbers matching
 *
 *  `/\d+/` matching integers matching
 *
 *  `/sqrt/`  matching square root function
 *  `/round/` matching routing function
 *  `/ceil/` matching rounding up function
 *  `/floor/` matching rounding down function
 *
 *  `/sin/` matching sine
 *  `/cos/` matching cosine
 *  `/tan/` matching tangent
 *  `/cot/` matching cotangent
 *
 *  `/sind/` matching sine (argument in degrees)
 *  `/cosd/` matching cosine (argument in degrees)
 *  `/tand/` matching tangent (argument in degrees)
 *  `/cotd/` matching cotangent (argument in degrees)
 *
 *  `/sinh/` matching hyperbolic sine
 *  `/cosh/` matching hyperbolic cosine
 *  `/tanh/` matching hyperbolic tangent
 *  `/coth/` matching hyperbolic cotangent
 *
 *  `/arcsin|asin/` matching inverse sine
 *  `/arccos|acos/` matching inverse cosine
 *  `/arctan|atan/` matching inverse tangent
 *  `/arccot|acot/` matching inverse cotangent
 *
 *  `/arsinh|arcsinh|asinh/` matching inverse hyperbolic sine
 *  `/arcosh|arccosh|acosh/` matching inverse hyperbolic cosine
 *  `/artanh|arctanh|atanh/` matching inverse hyperbolic tangent
 *  `/arcoth|arccoth|acoth/` matching inverse hyperbolic cotangent
 *
 *  `/exp/` matching exponential function
 *  `/log10|lg/` matching logarithm (base 10)
 *  `/log|ln/` matching natural logarithm
 *
 *  `/abs/` matching modulus (absolute value)
 *  `/sgn/` matching signum function
 *
 *  `/\(/` matching opening parenthesis (both as delimiter and function evaluation)
 *  `/\)/` matching closing parenthesis (both as delimiter and function evaluation)
 *  `/\{/` matching opening brace
 *  `/\}/` matching closing brace
 *
 *  `/\+/` matching + for addition (or unary +)
 *  `/\-/` matching - for subtraction (or unary -)
 *  `/\* /` matching * for multiplication
 *  `/\//` matching / for division
 *  `/\^/` matching ^ for exponentiation
 *
 *  `/\!/\!/` matching !! for semi-factorial
 *  `/\!/` matching ! for factorial
 *
 *  `/\NAN/` matching for a not a number
 *  `/\INF/` matching for infinite
 *
 *  `/pi/` matching constant pi
 *
 *  `/\;/` matching semicolon
 *  `/\n/` matching newline
 *
 *  `/\s+/` matching whitespace
 */
class AbstractLexer extends Lexer
{
    public function __construct()
    {
        $this->add(new TokenDefinition('/\d+[,\.]\d+(e[+-]?\d+)?/', TokenType::REAL_NUMBER));

        $this->add(new TokenDefinition('/\d+/', TokenType::NATURAL_NUMBER));

        $this->add(new TokenDefinition('/\d*(\.\d\d)/', TokenType::STRING));

        $this->add(new TokenDefinition('/sqrt/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/round/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/ceil/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/floor/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/ending/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/sind/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/cosd/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/tand/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/cotd/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/sinh/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/cosh/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/tanh/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/coth/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/sin/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/cos/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/tan/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/cot/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/arcsin|asin/', TokenType::FUNCTION_NAME, 'arcsin'));
        $this->add(new TokenDefinition('/arccos|acos/', TokenType::FUNCTION_NAME, 'arccos'));
        $this->add(new TokenDefinition('/arctan|atan/', TokenType::FUNCTION_NAME, 'arctan'));
        $this->add(new TokenDefinition('/arccot|acot/', TokenType::FUNCTION_NAME, 'arccot'));

        $this->add(new TokenDefinition('/arsinh|arcsinh|asinh/', TokenType::FUNCTION_NAME, 'arsinh'));
        $this->add(new TokenDefinition('/arcosh|arccosh|acosh/', TokenType::FUNCTION_NAME, 'arcosh'));
        $this->add(new TokenDefinition('/artanh|arctanh|atanh/', TokenType::FUNCTION_NAME, 'artanh'));
        $this->add(new TokenDefinition('/arcoth|arccoth|acoth/', TokenType::FUNCTION_NAME, 'arcoth'));

        $this->add(new TokenDefinition('/exp/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/log10|lg/', TokenType::FUNCTION_NAME, 'lg'));
        $this->add(new TokenDefinition('/log/', TokenType::FUNCTION_NAME, 'log'));
        $this->add(new TokenDefinition('/ln/', TokenType::FUNCTION_NAME, 'ln'));

        $this->add(new TokenDefinition('/abs/', TokenType::FUNCTION_NAME));
        $this->add(new TokenDefinition('/sgn/', TokenType::FUNCTION_NAME));

        $this->add(new TokenDefinition('/\(/', TokenType::OPEN_PARENTHESIS));
        $this->add(new TokenDefinition('/\)/', TokenType::CLOSE_PARENTHESIS));
        $this->add(new TokenDefinition('/\{/', TokenType::OPEN_BRACE));
        $this->add(new TokenDefinition('/\}/', TokenType::CLOSE_BRACE));

        $this->add(new TokenDefinition('/\+/', TokenType::ADDITION_OPERATOR));
        $this->add(new TokenDefinition('/\-/', TokenType::SUBTRACTION_OPERATOR));
        $this->add(new TokenDefinition('/\*/', TokenType::MULTIPLICATION_OPERATOR));
        $this->add(new TokenDefinition('/\//', TokenType::DIVISION_OPERATOR));
        $this->add(new TokenDefinition('/\^/', TokenType::EXPONENTIAL_OPERATOR));

        // Postfix operators
        $this->add(new TokenDefinition('/\!\!/', TokenType::SEMI_FACTORIAL_OPERATOR));
        $this->add(new TokenDefinition('/\!/', TokenType::FACTORIAL_OPERATOR));

        $this->add(new TokenDefinition('/NAN/', TokenType::CONSTANT));
        $this->add(new TokenDefinition('/INF/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/pi/', TokenType::CONSTANT));

        $this->add(new TokenDefinition('/\,/', TokenType::TERMINATOR));
        $this->add(new TokenDefinition('/\;/', TokenType::TERMINATOR));
        $this->add(new TokenDefinition('/\n/', TokenType::TERMINATOR));

        $this->add(new TokenDefinition('/\s+/', TokenType::WHITESPACE));
    }
}
