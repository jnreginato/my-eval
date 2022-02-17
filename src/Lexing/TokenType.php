<?php

declare(strict_types=1);

namespace MyEval\Lexing;

/**
 * Token type values
 */
final class TokenType
{
    // Operands

    /** Token representing a positive integer */
    public const NATURAL_NUMBER = 1;

    /** Token representing a (not necessarily positive) integer */
    public const INTEGER = 2;

    /** Token representing a rational number */
    public const RATIONAL_NUMBER = 3;

    /** Token representing a floating point number */
    public const REAL_NUMBER = 4;

    /** Token represented a boolean 'TRUE' or 'FALSE' */
    public const BOOLEAN = 5;

    /** Token representing an identifier, i.e. a variable name. */
    public const VARIABLE = 6;

    /** Token represented a known constant, e.g. 'pi' */
    public const CONSTANT = 7;

    // Prefix Operators (Unary expressions)

    /** Token representing a unary minus. Not used. This is the responsibility of the Parser */
    public const UNARY_MINUS = 101;

    /** Token represented a logical operator 'NOT' */
    public const NOT = 102;

    // Postfix Operators (Unary expressions)

    /** Token representing postfix factorial operator '!' */
    public const FACTORIAL_OPERATOR = 202;

    /** Token representing postfix sub-factorial operator '!!' */
    public const SEMI_FACTORIAL_OPERATOR = 203;

    // Infix Operators (Binary expressions)

    /** Token representing '+' */
    public const ADDITION_OPERATOR = 301;

    /** Token representing '-' */
    public const SUBTRACTION_OPERATOR = 302;

    /** Token representing '*' */
    public const MULTIPLICATION_OPERATOR = 303;

    /** Token representing '/' */
    public const DIVISION_OPERATOR = 304;

    /** Token representing '^' */
    public const EXPONENTIAL_OPERATOR = 305;

    /** Token represented a relational operator '==' */
    public const EQUAL_TO = 306;

    /** Token represented a relational operator '<>' */
    public const DIFFERENT_THAN = 307;

    /** Token represented a relational operator '>' */
    public const GREATER_THAN = 308;

    /** Token represented a relational operator '<' */
    public const LESS_THAN = 309;

    /** Token represented a relational operator '>=' */
    public const GREATER_OR_EQUAL_THAN = 310;

    /** Token represented a relational operator '<=' */
    public const LESS_OR_EQUAL_THAN = 311;

    /** Token represented a logical operator 'AND or &&' */
    public const AND                = 312;

    /** Token represented a logical operator 'OR, or ||' */
    public const OR                 = 313;

    // Ternary expressions

    /** Token represented a conditional operator 'if or ?' */
    public const IF                 = 401;

    /** Token represented a conditional operator 'else or :' */
    public const THEN = 402;

    /** Token represented a conditional operator 'else or :' */
    public const ELSE = 403;

    // Functions

    /** Token represented a function name, e.g. 'sin' */
    public const FUNCTION_NAME = 501;

    // Others

    /** Token representing an opening parenthesis, i.e. '(' */
    public const OPEN_PARENTHESIS = 601;

    /** Token representing a closing parenthesis, i.e. ')' */
    public const CLOSE_PARENTHESIS = 602;

    /** Token representing an opening brace. */
    public const OPEN_BRACE = 603;

    /** Token representing a closing brace. */
    public const CLOSE_BRACE = 604;

    /** Token representing white space, e.g. spaces and tabs. */
    public const WHITESPACE = 605;

    /** Token representing new line, e.g. \n. */
    public const NEW_LINE = 606;

    /** Token representing a terminator, e.g. ';'. */
    public const TERMINATOR = 999;

    /** Token representing a sentinel, for internal used in the Parser. Not used. */
    public const SENTINEL = 1000;
}
