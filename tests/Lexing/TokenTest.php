<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use PHPUnit\Framework\TestCase;

/**
 * Class TokenTest
 */
class TokenTest extends TestCase
{
    /**
     * @return void
     */
    public function testCanCreateToken(): void
    {
        $token = new Token('+', TokenType::ADDITION_OPERATOR);

        static::assertInstanceOf(Token::class, $token);
    }

    /**
     * @return void
     */
    public function testCanPrintToken(): void
    {
        $name = '+';
        $type = TokenType::ADDITION_OPERATOR;

        $token  = new Token($name, $type);
        $string = $token->__toString();

        static::assertEquals($name, $string);
    }

    /**
     * @return void
     */
    public function testCanFactorsInImplicitMultiplication(): void
    {
        $token1 = new Token('+', TokenType::ADDITION_OPERATOR);
        $token2 = new Token('1', TokenType::INTEGER);
        $token3 = new Token('sin', TokenType::FUNCTION_NAME);
        $token4 = new Token('(', TokenType::OPEN_PARENTHESIS);
        $token5 = new Token('x', TokenType::VARIABLE);

        static::assertFalse(Token::canFactorsInImplicitMultiplication(null, null));
        static::assertFalse(Token::canFactorsInImplicitMultiplication($token1, $token2));
        static::assertFalse(Token::canFactorsInImplicitMultiplication($token2, $token1));
        static::assertFalse(Token::canFactorsInImplicitMultiplication($token3, $token4));
        static::assertTrue(Token::canFactorsInImplicitMultiplication($token2, $token5));
    }
}
