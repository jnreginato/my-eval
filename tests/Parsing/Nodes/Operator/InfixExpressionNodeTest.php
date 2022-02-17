<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Extensions\Complex;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Solving\ASCIIPrinter;
use MyEval\Solving\ComplexEvaluator;
use MyEval\Solving\Differentiator;
use MyEval\Solving\LaTeXPrinter;
use MyEval\Solving\LogicEvaluator;
use MyEval\Solving\RationalEvaluator;
use MyEval\Solving\StdMathEvaluator;
use MyEval\Solving\TreePrinter;
use PHPUnit\Framework\TestCase;

/**
 * Class InfixExpressionNodeTest
 */
class InfixExpressionNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(
            new InfixExpressionNode('+'),
            Node::factory(new Token('+', TokenType::ADDITION_OPERATOR))
        );
        static::assertEquals(
            new InfixExpressionNode('-'),
            Node::factory(new Token('-', TokenType::SUBTRACTION_OPERATOR))
        );
        static::assertEquals(
            new InfixExpressionNode('*'),
            Node::factory(new Token('*', TokenType::MULTIPLICATION_OPERATOR))
        );
        static::assertEquals(
            new InfixExpressionNode('/'),
            Node::factory(new Token('/', TokenType::DIVISION_OPERATOR))
        );
        static::assertEquals(
            new InfixExpressionNode('^'),
            Node::factory(new Token('^', TokenType::EXPONENTIAL_OPERATOR))
        );
        static::assertEquals(
            new InfixExpressionNode('='),
            Node::factory(new Token('=', TokenType::EQUAL_TO))
        );
        static::assertEquals(
            new InfixExpressionNode('>'),
            Node::factory(new Token('>', TokenType::GREATER_THAN))
        );
        static::assertEquals(
            new InfixExpressionNode('<'),
            Node::factory(new Token('<', TokenType::LESS_THAN))
        );
        static::assertEquals(
            new InfixExpressionNode('<>'),
            Node::factory(new Token('<>', TokenType::DIFFERENT_THAN))
        );
        static::assertEquals(
            new InfixExpressionNode('>='),
            Node::factory(new Token('>=', TokenType::GREATER_OR_EQUAL_THAN))
        );
        static::assertEquals(
            new InfixExpressionNode('<='),
            Node::factory(new Token('<=', TokenType::LESS_OR_EQUAL_THAN))
        );
        static::assertEquals(
            new InfixExpressionNode('&&'),
            Node::factory(new Token('&&', TokenType::AND))
        );
        static::assertEquals(
            new InfixExpressionNode('||'),
            Node::factory(new Token('||', TokenType::OR))
        );
        static::assertEquals(
            new InfixExpressionNode('NOT'),
            Node::factory(new Token('NOT', TokenType::NOT))
        );
    }

    /**
     * @return InfixExpressionNode
     */
    public function testCanEmitExceptionIfUnknownOperator(): InfixExpressionNode
    {
        $this->expectException(UnknownOperatorException::class);
        return new InfixExpressionNode('@');
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(3.0, (new InfixExpressionNode('+', 1, 2))->evaluate());
        static::assertSame(1.0, (new InfixExpressionNode('-', 2.0, 1.0))->evaluate());
        static::assertSame(10.0, (new InfixExpressionNode('*', 2, 5))->evaluate());
        static::assertSame(2.0, (new InfixExpressionNode('/', 10, 5))->evaluate());
        static::assertSame(8.0, (new InfixExpressionNode('^', 2, 3))->evaluate());
        static::assertSame(5.0, (new InfixExpressionNode('+', new IntegerNode(2), new FloatNode(3)))->evaluate());
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(100.0, (new InfixExpressionNode('*', 10, 10))->accept(new StdMathEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): void
    {
        static::assertEquals(
            new RationalNode(1, 2),
            (new InfixExpressionNode('/', 1, 2))->accept(new RationalEvaluator())
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(
            new Complex(1, 0),
            (new InfixExpressionNode('-', 101, 100))->accept(new ComplexEvaluator())
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(
            new IntegerNode(0),
            (new InfixExpressionNode('+', -2, 2))->accept(new Differentiator('x'))
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertTrue((new InfixExpressionNode('=', 1, 1))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('2+1', (new InfixExpressionNode('+', 2, 1))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('2+1', (new InfixExpressionNode('+', 2, 1))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('(+, 2:int, 1:int)', (new InfixExpressionNode('+', 2, 1))->accept(new TreePrinter()));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanCompareTwoEqualNumberNodes(): void
    {
        $node  = new InfixExpressionNode('+', 2, 1);
        $other = new InfixExpressionNode('+', 2, 1);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanCompareTwoDifferentNumberNodes(): void
    {
        $node  = new InfixExpressionNode('+', 2, 1);
        $other = new InfixExpressionNode('-', 2, 1);
        static::assertFalse($node->compareTo($other));

        $node  = new InfixExpressionNode('+', null, null);
        $other = new InfixExpressionNode('+', 2, 1);
        static::assertFalse($node->compareTo($other));

        $node  = new InfixExpressionNode('+', 2, null);
        $other = new InfixExpressionNode('+', 2, 1);
        static::assertFalse($node->compareTo($other));

        $node  = new InfixExpressionNode('+', null, 1);
        $other = new InfixExpressionNode('+', 2, 1);
        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new InfixExpressionNode('+', 2, 1);
        $other = new IntegerNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(3, (new InfixExpressionNode('+', 2, 1))->complexity());
        static::assertEquals(5, (new InfixExpressionNode('/', 2, 1))->complexity());
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('2+1', (string)new InfixExpressionNode('+', 2, 1));
    }

    /**
     * @throws UnknownOperatorException
     */
    public function testCanBeUnary(): void
    {
        $node = new InfixExpressionNode('+', 2);
        static::assertTrue($node->canBeUnary());

        $node = new InfixExpressionNode('-', 2);
        static::assertTrue($node->canBeUnary());

        $node = new InfixExpressionNode('~', 2);
        static::assertTrue($node->canBeUnary());

        $node = new InfixExpressionNode('*', 2);
        static::assertFalse($node->canBeUnary());
    }

    /**
     * @throws UnknownOperatorException
     */
    public function testLowerPrecedence(): void
    {
        $node0 = new IntegerNode(2);
        $node1 = new InfixExpressionNode('+', 2, 1);
        $node2 = new InfixExpressionNode('/', 2, 1);
        $node3 = new InfixExpressionNode('^', 2, 1);

        static::assertFalse($node1->lowerPrecedenceThan($node0));
        static::assertTrue($node1->lowerPrecedenceThan($node2));
        static::assertFalse($node2->lowerPrecedenceThan($node1));
        static::assertTrue($node2->lowerPrecedenceThan($node2));
        static::assertFalse($node3->lowerPrecedenceThan($node3));
    }

    /**
     * @throws UnknownOperatorException
     */
    public function testStrictlyLowerPrecedence(): void
    {
        $node0 = new IntegerNode(2);
        $node1 = new InfixExpressionNode('+', 2, 1);
        $node2 = new InfixExpressionNode('/', 2, 1);

        static::assertFalse($node1->strictlyLowerPrecedenceThan($node0));
        static::assertTrue($node1->strictlyLowerPrecedenceThan($node2));
        static::assertFalse($node2->strictlyLowerPrecedenceThan($node1));
    }
}
