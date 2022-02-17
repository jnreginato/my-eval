<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use MyEval\Exceptions\UnknownTokenException;
use PHPUnit\Framework\TestCase;

/**
 * Class StdMathLexerTest
 */
class LogicLexerTest extends TestCase
{
    private LogicLexer $lexer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->lexer = new LogicLexer();

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeIf(): void
    {
        $tokens = $this->lexer->tokenize('IF');
        $this->assertTokenEquals('IF', TokenType::IF, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeSemicolon(): void
    {
        $tokens = $this->lexer->tokenize(';');
        $this->assertTokenEquals(';', TokenType::TERMINATOR, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeOperator(): void
    {
        $tokens = $this->lexer->tokenize('=');
        $this->assertTokenEquals('=', TokenType::EQUAL_TO, $tokens[0]);

        $tokens = $this->lexer->tokenize('<>');
        $this->assertTokenEquals('<>', TokenType::DIFFERENT_THAN, $tokens[0]);

        $tokens = $this->lexer->tokenize('>=');
        $this->assertTokenEquals('>=', TokenType::GREATER_OR_EQUAL_THAN, $tokens[0]);

        $tokens = $this->lexer->tokenize('<=');
        $this->assertTokenEquals('<=', TokenType::LESS_OR_EQUAL_THAN, $tokens[0]);

        $tokens = $this->lexer->tokenize('>');
        $this->assertTokenEquals('>', TokenType::GREATER_THAN, $tokens[0]);

        $tokens = $this->lexer->tokenize('<');
        $this->assertTokenEquals('<', TokenType::LESS_THAN, $tokens[0]);

        $tokens = $this->lexer->tokenize('&&');
        $this->assertTokenEquals('&&', TokenType::AND, $tokens[0]);

        $tokens = $this->lexer->tokenize('||');
        $this->assertTokenEquals('||', TokenType::OR, $tokens[0]);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testCanTokenizeNumbersAndOperators(): void
    {
        $tokens = $this->lexer->tokenize('3>=5');

        static::assertCount(3, $tokens);

        $this->assertTokenEquals('3', TokenType::NATURAL_NUMBER, $tokens[0]);
        $this->assertTokenEquals('>=', TokenType::GREATER_OR_EQUAL_THAN, $tokens[1]);
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
        $tokens = $this->lexer->tokenize('variable');
        static::assertCount(1, $tokens);
        $this->assertTokenEquals('variable', TokenType::VARIABLE, $tokens[0]);

        $tokens = $this->lexer->tokenize('IF (5>4); 9; 8');
        static::assertCount(13, $tokens);
        $this->assertTokenEquals('IF', TokenType::IF, $tokens[0]);
        $this->assertTokenEquals(' ', TokenType::WHITESPACE, $tokens[1]);
        $this->assertTokenEquals('(', TokenType::OPEN_PARENTHESIS, $tokens[2]);
        $this->assertTokenEquals('5', TokenType::NATURAL_NUMBER, $tokens[3]);
        $this->assertTokenEquals('>', TokenType::GREATER_THAN, $tokens[4]);
        $this->assertTokenEquals('4', TokenType::NATURAL_NUMBER, $tokens[5]);
        $this->assertTokenEquals(')', TokenType::CLOSE_PARENTHESIS, $tokens[6]);
        $this->assertTokenEquals(';', TokenType::TERMINATOR, $tokens[7]);
        $this->assertTokenEquals(' ', TokenType::WHITESPACE, $tokens[8]);
        $this->assertTokenEquals('9', TokenType::NATURAL_NUMBER, $tokens[9]);
        $this->assertTokenEquals(';', TokenType::TERMINATOR, $tokens[10]);
        $this->assertTokenEquals(' ', TokenType::WHITESPACE, $tokens[11]);
        $this->assertTokenEquals('8', TokenType::NATURAL_NUMBER, $tokens[12]);
    }
}
