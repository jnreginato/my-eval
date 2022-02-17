<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use MyEval\Exceptions\UnknownTokenException;
use PHPUnit\Framework\TestCase;

/**
 * Class ComplexLexerTest
 */
class ComplexLexerTest extends TestCase
{
    private ComplexMathLexer $lexer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->lexer = new ComplexMathLexer();

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeFunction(): void
    {
        $tokens = $this->lexer->tokenize('arg');
        $this->assertTokenEquals('arg', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('conj');
        $this->assertTokenEquals('conj', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('re');
        $this->assertTokenEquals('re', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('im');
        $this->assertTokenEquals('im', TokenType::FUNCTION_NAME, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeConstant(): void
    {
        $tokens = $this->lexer->tokenize('i');

        $this->assertTokenEquals('i', TokenType::CONSTANT, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeFullComplexNumber(): void
    {
        $tokens = $this->lexer->tokenize('3+5i');

        static::assertCount(4, $tokens);

        $this->assertTokenEquals('3', TokenType::NATURAL_NUMBER, $tokens[0]);
        $this->assertTokenEquals('+', TokenType::ADDITION_OPERATOR, $tokens[1]);
        $this->assertTokenEquals('5', TokenType::NATURAL_NUMBER, $tokens[2]);
        $this->assertTokenEquals('i', TokenType::CONSTANT, $tokens[3]);
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
}
