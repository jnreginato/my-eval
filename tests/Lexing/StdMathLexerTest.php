<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use MyEval\Exceptions\UnknownTokenException;
use PHPUnit\Framework\TestCase;

/**
 * Class StdMathLexerTest
 */
class StdMathLexerTest extends TestCase
{
    private StdMathLexer $lexer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->lexer = new StdMathLexer();

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeNumber(): void
    {
        $tokens = $this->lexer->tokenize('325');
        $this->assertTokenEquals('325', TokenType::NATURAL_NUMBER, $tokens[0]);

        $tokens = $this->lexer->tokenize('-5');
        static::assertCount(2, $tokens);
        $this->assertTokenEquals('-', TokenType::SUBTRACTION_OPERATOR, $tokens[0]);
        $this->assertTokenEquals('5', TokenType::NATURAL_NUMBER, $tokens[1]);

        $tokens = $this->lexer->tokenize('2.3');
        $this->assertTokenEquals('2.3', TokenType::REAL_NUMBER, $tokens[0]);

        $tokens = $this->lexer->tokenize('2.3e+3');
        $this->assertTokenEquals('2.3e+3', TokenType::REAL_NUMBER, $tokens[0]);

        $tokens = $this->lexer->tokenize('2.3e4');
        $this->assertTokenEquals('2.3e4', TokenType::REAL_NUMBER, $tokens[0]);

        $tokens = $this->lexer->tokenize('2.3e-2');
        $this->assertTokenEquals('2.3e-2', TokenType::REAL_NUMBER, $tokens[0]);

        $tokens = $this->lexer->tokenize('-2.3');
        static::assertCount(2, $tokens);
        $this->assertTokenEquals('-', TokenType::SUBTRACTION_OPERATOR, $tokens[0]);
        $this->assertTokenEquals('2.3', TokenType::REAL_NUMBER, $tokens[1]);

        $tokens = $this->lexer->tokenize('-2.3e1');
        static::assertCount(2, $tokens);
        $this->assertTokenEquals('-', TokenType::SUBTRACTION_OPERATOR, $tokens[0]);
        $this->assertTokenEquals('2.3e1', TokenType::REAL_NUMBER, $tokens[1]);
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
    public function testIdentifierTokens(): void
    {
        $tokens = $this->lexer->tokenize('xy');

        static::assertCount(2, $tokens);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[0]);
        $this->assertTokenEquals('y', TokenType::VARIABLE, $tokens[1]);

        $tokens = $this->lexer->tokenize('xsinx');

        static::assertCount(3, $tokens);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[0]);
        $this->assertTokenEquals('sin', TokenType::FUNCTION_NAME, $tokens[1]);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[2]);

        $tokens = $this->lexer->tokenize('xsix');

        static::assertCount(4, $tokens);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[0]);
        $this->assertTokenEquals('s', TokenType::VARIABLE, $tokens[1]);
        $this->assertTokenEquals('i', TokenType::VARIABLE, $tokens[2]);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[3]);

        $tokens = $this->lexer->tokenize('asin');

        static::assertCount(1, $tokens);
        $this->assertTokenEquals('arcsin', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('a sin');

        static::assertCount(3, $tokens);
        $this->assertTokenEquals('a', TokenType::VARIABLE, $tokens[0]);
        $this->assertTokenEquals(' ', TokenType::WHITESPACE, $tokens[1]);
        $this->assertTokenEquals('sin', TokenType::FUNCTION_NAME, $tokens[2]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testParenthesisTokens(): void
    {
        $tokens = $this->lexer->tokenize('(()');

        static::assertCount(3, $tokens);
        $this->assertTokenEquals('(', TokenType::OPEN_PARENTHESIS, $tokens[0]);
        $this->assertTokenEquals('(', TokenType::OPEN_PARENTHESIS, $tokens[1]);
        $this->assertTokenEquals(')', TokenType::CLOSE_PARENTHESIS, $tokens[2]);

        $tokens = $this->lexer->tokenize('(x+1)');
        static::assertCount(5, $tokens);
        $this->assertTokenEquals('(', TokenType::OPEN_PARENTHESIS, $tokens[0]);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[1]);
        $this->assertTokenEquals('+', TokenType::ADDITION_OPERATOR, $tokens[2]);
        $this->assertTokenEquals('1', TokenType::NATURAL_NUMBER, $tokens[3]);
        $this->assertTokenEquals(')', TokenType::CLOSE_PARENTHESIS, $tokens[4]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testWhitepsace(): void
    {
        $tokens = $this->lexer->tokenize("  x\t+\n ");

        static::assertCount(6, $tokens);
        $this->assertTokenEquals('  ', TokenType::WHITESPACE, $tokens[0]);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[1]);
        $this->assertTokenEquals("\t", TokenType::WHITESPACE, $tokens[2]);
        $this->assertTokenEquals('+', TokenType::ADDITION_OPERATOR, $tokens[3]);
        $this->assertTokenEquals("\n", TokenType::TERMINATOR, $tokens[4]);
        $this->assertTokenEquals(' ', TokenType::WHITESPACE, $tokens[5]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testArcsin(): void
    {
        $tokens = $this->lexer->tokenize('asin');
        $this->assertTokenEquals('arcsin', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('arcsin');
        $this->assertTokenEquals('arcsin', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('asin(x)');
        $this->assertTokenEquals('arcsin', TokenType::FUNCTION_NAME, $tokens[0]);
        $this->assertTokenEquals('(', TokenType::OPEN_PARENTHESIS, $tokens[1]);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[2]);
        $this->assertTokenEquals(')', TokenType::CLOSE_PARENTHESIS, $tokens[3]);

        $tokens = $this->lexer->tokenize('arcsin(x)');
        $this->assertTokenEquals('arcsin', TokenType::FUNCTION_NAME, $tokens[0]);
        $this->assertTokenEquals('(', TokenType::OPEN_PARENTHESIS, $tokens[1]);
        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[2]);
        $this->assertTokenEquals(')', TokenType::CLOSE_PARENTHESIS, $tokens[3]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testArccos(): void
    {
        $tokens = $this->lexer->tokenize('acos');
        $this->assertTokenEquals('arccos', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('arccos');
        $this->assertTokenEquals('arccos', TokenType::FUNCTION_NAME, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testArctan(): void
    {
        $tokens = $this->lexer->tokenize('atan');
        $this->assertTokenEquals('arctan', TokenType::FUNCTION_NAME, $tokens[0]);

        $tokens = $this->lexer->tokenize('arctan');
        $this->assertTokenEquals('arctan', TokenType::FUNCTION_NAME, $tokens[0]);
    }
}
