<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use MyEval\Exceptions\UnknownTokenException;

/**
 * Token definitions using regular expressions to match input.
 *
 * To get the Lexer to recognize tokens, they need to be defined. This is the task of the TokenDefinition class.
 * Each TokenDefinition consists of a regular expression used to match the input string, a corresponding token type and
 * an optional token value (making it possible to standardize the token value for synonyms, e.g. both ln and log can be
 * tokenized into the same token with value 'log'.)
 *
 * ## Example usage (except from StdMathLexer):
 *
 * $lexer = new Lexer();
 * $lexer->add(new TokenDefinition('/\d+\.\d+/', TokenType::RealNumber));
 * $lexer->add(new TokenDefinition('/\d+/', TokenType::PosInt));
 * $lexer->add(new TokenDefinition('/sin/', TokenType::FunctionName));
 * $lexer->add(new TokenDefinition('/arcsin|asin/', TokenType::FunctionName, 'arcsin'));
 * $lexer->add(new TokenDefinition('/\+/', TokenType::AdditionOperator));
 * $lexer->add(new TokenDefinition('/\-/', TokenType::SubtractionOperator));
 */
class TokenDefinition
{
    /**
     * @param string $pattern   Regular expression defining the rule for matching a token.
     * @param int    $tokenType Type of token, as defined in TokenType class.
     * @param string $value     Standardized value of token.
     */
    public function __construct(
        private string $pattern,
        private int $tokenType,
        private string $value = ''
    ) {
    }

    /**
     * Try to match the given input to the current TokenDefinition.
     *
     * @param string $input Input string.
     *
     * @return Token|null Token matching the regular expression defining the TokenDefinition.
     * @throws UnknownTokenException
     */
    public function match(string $input): ?Token
    {
        // Match the input with the regex pattern, storing any match found into the $matches variable, along with the
        // index of the string at which it was matched.
        $result = preg_match($this->pattern, $input, $matches, PREG_OFFSET_CAPTURE);

        // preg_match returns false if an error occurred
        if ($result === false) {
            throw new UnknownTokenException(preg_last_error_msg());
        }

        // 0 means no match was found
        if ($result === 0) {
            return null;
        }

        return $this->getTokenFromMatch($matches[0]);
    }

    /**
     * Convert matching string to an actual Token.
     *
     * @param array $match Matching text.
     *
     * @return Token|null Corresponding token
     */
    private function getTokenFromMatch(array $match): ?Token
    {
        [$matchValue, $offset] = $match;

        // If we don't match at the beginning of the string, it fails.
        if ((int)$offset !== 0) {
            return null;
        }

        if ($this->value) {
            $matchValue = $this->value;
        }

        return new Token($matchValue, $this->tokenType, $match[0]);
    }
}
