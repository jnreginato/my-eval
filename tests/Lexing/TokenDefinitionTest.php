<?php

declare(strict_types=1);

namespace MyEval\Lexing;

use MyEval\Exceptions\UnknownTokenException;
use PHPUnit\Framework\TestCase;

/**
 * Class TokenDefinitionTest
 */
class TokenDefinitionTest extends TestCase
{
    private TokenDefinition $tokenDefinition;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->tokenDefinition = new TokenDefinition('/\d+/', TokenType::NATURAL_NUMBER);

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testMatchReturnsTokenObjectIfMatchedInput(): void
    {
        $token = $this->tokenDefinition->match('123');

        static::assertInstanceOf(Token::class, $token);

        static::assertEquals('123', $token->value);
        static::assertEquals(TokenType::NATURAL_NUMBER, $token->type);
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testNoMatchReturnsNull(): void
    {
        static::assertNull($this->tokenDefinition->match('@'));
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testMatchReturnsNullIfOffsetNotZero(): void
    {
        static::assertNull($this->tokenDefinition->match('@123'));
    }

    /**
     * @return void
     * @throws UnknownTokenException
     */
    public function testMatchError(): void
    {
        $tokenDef = new TokenDefinition('/(?:\D+|<\d+>)*[!?]/', TokenType::VARIABLE);

        $this->expectException(UnknownTokenException::class);
        $tokenDef->match('foobar foobar foobar');
    }
}
