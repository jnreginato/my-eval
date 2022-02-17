<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use function strlen;

/**
 * Class to handle tokens, i.e. discrete pieces of the input string that has specific meaning.
 */
class Token
{
    /**
     * Create a token with a given value and type, as well as an optional 'match' which is the actual character string
     * matching the token definition. Most of the time, $value and $match are the same, but in order to handle token
     * synonyms, they may be different.
     *
     * As an example illustrating the above, the natural logarithm can be denoted ln() as well as log(). In order to
     * standardize the token list, both inputs might generate a token with value 'log' and type TokenType::FunctionName,
     * but the match parameter will be the actual string matched, i.e. 'log' and 'ln', respectively, so that the token
     * knows its own length so that the rest of the input string will be handled correctly.
     *
     * @param string      $value Standardized value of Token.
     * @param int         $type  Token type, as defined by the TokenType class.
     * @param string|null $match Optional actual match in the input string.
     */
    public function __construct(
        public readonly string $value,
        public readonly int $type,
        private string|null $match = null
    ) {
        $this->match = $match ?: $value;
    }

    /**
     * Length of the input string matching the token.
     *
     * @return int Length of string matching the token.
     */
    public function length(): int
    {
        return strlen($this->match);
    }

    /**
     * Helper function, converting the Token to a printable string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Helper function, determining whether a pair of tokens can form an implicit multiplication.
     *
     * Mathematical shorthand writing often leaves out explicit multiplication symbols, writing "2x" instead of "2*x"
     * or "2 \cdot x". The parser accepts implicit multiplication if the first token is a nullary operator or a closing
     * parenthesis, and the second token is a nullary operator or an opening parenthesis. (Unless the first token is a
     * function name, and the second is an opening parenthesis.)
     *
     * @param Token|null $token1
     * @param Token|null $token2
     *
     * @return bool
     */
    public static function canFactorsInImplicitMultiplication(?Token $token1, ?Token $token2): bool
    {
        if (
            ($token1 === null || $token2 === null) ||
            ($token1->type === TokenType::FUNCTION_NAME && $token2->type === TokenType::OPEN_PARENTHESIS) ||
            (!static::checkToken1($token1) || !static::checkToken2($token2))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if token 1 is eligible for implicit multiplication.
     *
     * @param Token $token1
     *
     * @return bool
     */
    private static function checkToken1(Token $token1): bool
    {
        return (
            $token1->type === TokenType::NATURAL_NUMBER ||
            $token1->type === TokenType::INTEGER ||
            $token1->type === TokenType::REAL_NUMBER ||
            $token1->type === TokenType::CONSTANT ||
            $token1->type === TokenType::VARIABLE ||
            $token1->type === TokenType::FUNCTION_NAME ||
            $token1->type === TokenType::CLOSE_PARENTHESIS ||
            $token1->type === TokenType::FACTORIAL_OPERATOR ||
            $token1->type === TokenType::SEMI_FACTORIAL_OPERATOR
        );
    }

    /**
     * Checks if token 2 is eligible for implicit multiplication.
     *
     * @param Token $token2
     *
     * @return bool
     */
    private static function checkToken2(Token $token2): bool
    {
        return (
            $token2->type === TokenType::NATURAL_NUMBER ||
            $token2->type === TokenType::INTEGER ||
            $token2->type === TokenType::REAL_NUMBER ||
            $token2->type === TokenType::CONSTANT ||
            $token2->type === TokenType::VARIABLE ||
            $token2->type === TokenType::FUNCTION_NAME ||
            $token2->type === TokenType::OPEN_PARENTHESIS
        );
    }
}
