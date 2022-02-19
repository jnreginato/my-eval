<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Extensions\Complex;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Solving\ASCIIPrinter;
use MyEval\Solving\ComplexEvaluator;
use MyEval\Solving\Differentiator;
use MyEval\Solving\LaTeXPrinter;
use MyEval\Solving\LogicEvaluator;
use MyEval\Solving\RationalEvaluator;
use MyEval\Solving\StdMathEvaluator;
use MyEval\Solving\TreePrinter;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Class FunctionNodeTest
 */
class FunctionNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(
            new FunctionNode('sin'),
            Node::factory(new Token('sin', TokenType::FUNCTION_NAME))
        );
    }

    /**
     * @return void
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(1.0, (new FunctionNode('sin', [new FloatNode(M_PI / 2)]))->evaluate());
        static::assertSame(0.5, (new FunctionNode('cos', [new FloatNode(M_PI / 3)]))->evaluate());
        static::assertSame(5.0, (new FunctionNode('sqrt', [new IntegerNode(25)]))->evaluate());
        static::assertSame(log(100), (new FunctionNode('log', [new IntegerNode(100)]))->evaluate());
        static::assertSame(exp(M_PI), (new FunctionNode('exp', [new ConstantNode('pi')]))->evaluate());
        static::assertSame(3.0, (new FunctionNode('ceil', [new FloatNode(2.3)]))->evaluate());
    }

    /**
     * @return void
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(1.0, (new FunctionNode('sin', [new FloatNode(M_PI / 2)]))->accept(new StdMathEvaluator()));
    }

    /**
     * @return float
     */
    public function testCanAcceptRationalEvaluatorVisitor(): float
    {
        $this->expectException(UnexpectedValueException::class);
        return (new FunctionNode('sin', [new FloatNode(M_PI / 2)]))->accept(new RationalEvaluator());
    }

    /**
     * @return void
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(
            new Complex(1, 0),
            (new FunctionNode('sin', [new FloatNode(M_PI / 2)]))->accept(new ComplexEvaluator())
        );
    }

    /**
     * @return void
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(
            new IntegerNode(0),
            (new FunctionNode('sqrt', [new IntegerNode(25)]))->accept(new Differentiator('x'))
        );
    }

    /**
     * @return void
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(1.0, (new FunctionNode('sin', [new FloatNode(M_PI / 2)]))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('log(100)', (new FunctionNode('log', [new IntegerNode(100)]))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('\log(100)', (new FunctionNode('log', [new IntegerNode(100)]))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame(
            'log(100:int)',
            (new FunctionNode('log', [new IntegerNode(100)]))->accept(new TreePrinter())
        );
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNodes(): void
    {
        $node  = new FunctionNode('sin', [new FloatNode(M_PI / 6)]);
        $other = new FunctionNode('sin', [new FloatNode(M_PI / 6)]);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new FunctionNode('sin', [new FloatNode(M_PI / 6)]);
        $other = new FunctionNode('cos', [new FloatNode(M_PI / 6)]);
        static::assertFalse($node->compareTo($other));

        $node  = new FunctionNode('sin', [new FloatNode(M_PI / 6)]);
        $other = new IntegerNode(1);
        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(7, (new FunctionNode('sin', [new FloatNode(M_PI / 6)]))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('sin(3.1415926535898)', (string)new FunctionNode('sin', [new FloatNode(M_PI)]));
    }

    /**
     * @return void
     */
    public function testCanSetOperand(): void
    {
        $node = new FunctionNode('sqrt', [new IntegerNode(16)]);
        static::assertEquals(4, $node->accept(new StdMathEvaluator()));

        $node->setOperand(25);
        static::assertEquals(5, $node->accept(new StdMathEvaluator()));

        $node->setOperand(25.0);
        static::assertEquals(5, $node->accept(new StdMathEvaluator()));

        $node->setOperand(new IntegerNode(25));
        static::assertEquals(5, $node->accept(new StdMathEvaluator()));
    }
}
