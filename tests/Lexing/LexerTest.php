<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use MyEval\Exceptions\UnknownTokenException;
use PHPUnit\Framework\TestCase;

/**
 * Class LexerTest
 */
class LexerTest extends TestCase
{
    private Lexer $lexer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $lexer = new Lexer();
        $lexer->add(new TokenDefinition('/\d+/', TokenType::NATURAL_NUMBER));
        $lexer->add(new TokenDefinition('/\+/', TokenType::ADDITION_OPERATOR));

        $this->lexer = $lexer;

        parent::setUp();
    }

    /**
     * @param string $value
     * @param int    $type
     * @param Token  $token
     *
     * @return void
     */
    private function assertTokenEquals(string $value, int $type, Token $token): void
    {
        static::assertEquals($value, $token->value);
        static::assertEquals($type, $token->type);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeNumber(): void
    {
        $tokens = $this->lexer->tokenize('325');

        $this->assertTokenEquals('325', TokenType::NATURAL_NUMBER, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeOperator(): void
    {
        $tokens = $this->lexer->tokenize('+');

        $this->assertTokenEquals('+', TokenType::ADDITION_OPERATOR, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeNumbersAndOperators(): void
    {
        $tokens = $this->lexer->tokenize('3+5');

        static::assertCount(3, $tokens);

        $this->assertTokenEquals('3', TokenType::NATURAL_NUMBER, $tokens[0]);
        $this->assertTokenEquals('+', TokenType::ADDITION_OPERATOR, $tokens[1]);
        $this->assertTokenEquals('5', TokenType::NATURAL_NUMBER, $tokens[2]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testExceptionIsThrownOnUnknownToken(): void
    {
        $this->expectException(UnknownTokenException::class);

        $this->lexer->tokenize('@');
    }
}
