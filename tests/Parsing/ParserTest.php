<?php

declare(strict_types=1);

namespace MyEval\Parsing;

use MyEval\Exceptions\DelimeterMismatchException;
use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 */
class ParserTest extends TestCase
{
    private Parser $parser;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser = new Parser();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseNaturalNumberToken(): void
    {
        $tokens = [
            new Token('1', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(1), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseNaturalNumberTokenWithDebug(): void
    {
        $tokens = [
            new Token('1', TokenType::NATURAL_NUMBER),
        ];

        $this->parser->setDebugMode(true);
        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(1), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseIntegerToken(): void
    {
        $tokens = [
            new Token('-2', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(-2), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseRealNumberToken(): void
    {
        $tokens = [
            new Token('1.5', TokenType::REAL_NUMBER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new FloatNode(1.5), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseBooleanToken(): void
    {
        $tokens = [
            new Token('false', TokenType::BOOLEAN),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new BooleanNode('false'), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseVariableToken(): void
    {
        $tokens = [
            new Token('x', TokenType::VARIABLE),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new VariableNode('x'), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseConstantToken(): void
    {
        $tokens = [
            new Token('pi', TokenType::CONSTANT),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new ConstantNode('pi'), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseAdditionInlineExpressionToken(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('+', TokenType::ADDITION_OPERATOR),
            new Token('1', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(3), $node);

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(new InfixExpressionNode('+', new IntegerNode(2), new IntegerNode(1)), $node);
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseSubtractionInlineExpressionToken(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('-', TokenType::ADDITION_OPERATOR),
            new Token('1', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(1), $node);

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(new InfixExpressionNode('-', new IntegerNode(2), new IntegerNode(1)), $node);
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseMultiplicationInlineExpressionToken(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('*', TokenType::MULTIPLICATION_OPERATOR),
            new Token('1', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(2), $node);

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(new InfixExpressionNode('*', new IntegerNode(2), new IntegerNode(1)), $node);
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseMultiplicationInlineExpressionTokenWithImplicitOperator(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('2', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(4), $node);

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(new InfixExpressionNode('*', new IntegerNode(2), new IntegerNode(2)), $node);
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseDivisionInlineExpressionToken(): void
    {
        $tokens = [
            new Token('2.0', TokenType::REAL_NUMBER),
            new Token('/', TokenType::DIVISION_OPERATOR),
            new Token('2', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new FloatNode(1.0), $node);

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(new InfixExpressionNode('/', new FloatNode(2.0), new IntegerNode(2)), $node);
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseExponentialInlineExpressionToken(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('^', TokenType::EXPONENTIAL_OPERATOR),
            new Token('3', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(8), $node);

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(new InfixExpressionNode('^', new IntegerNode(2), new IntegerNode(3)), $node);
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseInlineExpressionTokenWithConstants(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('pi', TokenType::CONSTANT),
            new Token('^', TokenType::EXPONENTIAL_OPERATOR),
            new Token('2', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(
            new InfixExpressionNode(
                '*',
                new IntegerNode(2),
                new InfixExpressionNode('^', new ConstantNode('pi'), new IntegerNode(2))
            ),
            $node
        );
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseInlineExpressionTokenWithVariables(): void
    {
        $tokens = [
            new Token('2', TokenType::INTEGER),
            new Token('x', TokenType::VARIABLE),
            new Token('^', TokenType::EXPONENTIAL_OPERATOR),
            new Token('2', TokenType::INTEGER),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(
            new InfixExpressionNode(
                '*',
                new IntegerNode(2),
                new InfixExpressionNode('^', new VariableNode('x'), new IntegerNode(2))
            ),
            $node
        );
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParsePostfixExpressionToken(): void
    {
        $tokens = [
            new Token('3', TokenType::INTEGER),
            new Token('!', TokenType::FACTORIAL_OPERATOR),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new FunctionNode('!', new IntegerNode(3)), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanEmitExceptionOnParsePostfixExpressionToken(): void
    {
        $tokens = [
            new Token('!', TokenType::FACTORIAL_OPERATOR),
        ];

        $this->expectException(SyntaxErrorException::class);
        $this->parser->parse($tokens);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseTernaryToken(): void
    {
        $tokens = [
            new Token('if', TokenType::IF),
            new Token('(', TokenType::OPEN_PARENTHESIS),
            new Token('x', TokenType::VARIABLE),
            new Token('>', TokenType::EQUAL_TO),
            new Token('2', TokenType::INTEGER),
            new Token(')', TokenType::CLOSE_PARENTHESIS),
            new Token('{', TokenType::OPEN_BRACE),
            new Token('1', TokenType::INTEGER),
            new Token('+', TokenType::ADDITION_OPERATOR),
            new Token('1', TokenType::INTEGER),
            new Token(';', TokenType::TERMINATOR),
            new Token('}', TokenType::CLOSE_BRACE),
            new Token('else', TokenType::ELSE),
            new Token('{', TokenType::OPEN_BRACE),
            new Token('2', TokenType::INTEGER),
            new Token('^', TokenType::EXPONENTIAL_OPERATOR),
            new Token('3', TokenType::INTEGER),
            new Token(';', TokenType::TERMINATOR),
            new Token('}', TokenType::CLOSE_BRACE),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(
            new TernaryExpressionNode(
                new InfixExpressionNode('>', new VariableNode('x'), new IntegerNode(2)),
                new IntegerNode(2),
                new IntegerNode(8)
            ),
            $node
        );

        $this->parser->setSimplifying(false);
        $node = $this->parser->parse($tokens);
        static::assertEquals(
            new TernaryExpressionNode(
                new InfixExpressionNode('>', new VariableNode('x'), new IntegerNode(2)),
                new InfixExpressionNode('+', new IntegerNode(1), new IntegerNode(1)),
                new InfixExpressionNode('^', new IntegerNode(2), new IntegerNode(3)),
            ),
            $node
        );
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseFunctionToken(): void
    {
        $tokens = [
            new Token('sin', TokenType::FUNCTION_NAME),
            new Token('(', TokenType::OPEN_PARENTHESIS),
            new Token('pi', TokenType::CONSTANT),
            new Token('/', TokenType::DIVISION_OPERATOR),
            new Token('3', TokenType::INTEGER),
            new Token(')', TokenType::CLOSE_PARENTHESIS),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(
            new FunctionNode(
                'sin',
                new InfixExpressionNode(
                    '/',
                    new ConstantNode('pi'),
                    new IntegerNode(3)
                ),
            ),
            $node
        );
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseUnaryToken(): void
    {
        $tokens = [
            new Token('1', TokenType::INTEGER),
            new Token('+', TokenType::ADDITION_OPERATOR),
            new Token('(', TokenType::OPEN_PARENTHESIS),
            new Token('-', TokenType::SUBTRACTION_OPERATOR),
            new Token('2', TokenType::INTEGER),
            new Token(')', TokenType::CLOSE_PARENTHESIS),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(-1), $node);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     */
    public function testCanParseUnmappedToken(): void
    {
        $tokens = [
            new Token('1', TokenType::NATURAL_NUMBER),
            new Token("\n", TokenType::NEW_LINE),
        ];

        $node = $this->parser->parse($tokens);

        static::assertEquals(new IntegerNode(1), $node);
    }

    /**
     * @throws ExponentialException
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     */
    public function testCanEmitErrorAtOpenParenthesisNotMatched(): void
    {
        $tokens = [
            new Token('1', TokenType::INTEGER),
            new Token(')', TokenType::CLOSE_BRACE),
        ];

        $this->expectException(DelimeterMismatchException::class);
        $this->parser->parse($tokens);
    }

    /**
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     */
    public function testCanEmitErrorAtOpenBraceNotMatched(): void
    {
        $tokens = [
            new Token('1', TokenType::INTEGER),
            new Token('}', TokenType::CLOSE_BRACE),
        ];

        $this->expectException(DelimeterMismatchException::class);
        $this->parser->parse($tokens);
    }
}
