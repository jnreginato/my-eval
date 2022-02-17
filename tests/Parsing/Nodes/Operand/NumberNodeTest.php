<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Extensions\Complex;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Node;
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
 * Class NumberNodeTest
 */
class NumberNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        $token0 = new Token('0.0', TokenType::REAL_NUMBER);
        $token1 = new Token('1', TokenType::REAL_NUMBER);
        $token2 = new Token('1.0', TokenType::REAL_NUMBER);
        $token3 = new Token('-1.0', TokenType::REAL_NUMBER);

        static::assertEquals(new FloatNode(0), Node::factory($token0));
        static::assertEquals(new FloatNode(1), Node::factory($token1));
        static::assertEquals(new FloatNode(1), Node::factory($token2));
        static::assertEquals(new FloatNode(-1), Node::factory($token3));
    }

    /**
     * @return void
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(0.0, (new FloatNode(0))->evaluate());
        static::assertSame(1.0, (new FloatNode(1))->evaluate());
        static::assertSame(-1.0, (new FloatNode(-1))->evaluate());
        static::assertSame(100.0, (new FloatNode(100))->evaluate());
    }

    /**
     * @return void
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(0.0, (new FloatNode(0))->accept(new StdMathEvaluator()));
        static::assertSame(1.0, (new FloatNode(1))->accept(new StdMathEvaluator()));
        static::assertSame(-1.0, (new FloatNode(-1))->accept(new StdMathEvaluator()));
        static::assertSame(99.5, (new FloatNode(99.5))->accept(new StdMathEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): void
    {
        $this->expectException(UnexpectedValueException::class);
        static::assertEquals(new RationalNode(0, 1), (new FloatNode(0))->accept(new RationalEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(new Complex(0, 0), (new FloatNode(0))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(1.2, 0), (new FloatNode(1.2))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(-1.2, 0), (new FloatNode(-1.2))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(99.5, 0), (new FloatNode(99.5))->accept(new ComplexEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(new IntegerNode(0), (new FloatNode(0))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new FloatNode(1.2))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new FloatNode(-1.2))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new FloatNode(99.5))->accept(new Differentiator('x')));
    }

    /**
     * @return void
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(0.0, (new FloatNode(0))->accept(new LogicEvaluator()));
        static::assertSame(1.2, (new FloatNode(1.2))->accept(new LogicEvaluator()));
        static::assertSame(-1.2, (new FloatNode(-1.2))->accept(new LogicEvaluator()));
        static::assertSame(99.5, (new FloatNode(99.5))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('0', (new FloatNode(0))->accept(new ASCIIPrinter()));
        static::assertSame('1.2', (new FloatNode(1.2))->accept(new ASCIIPrinter()));
        static::assertSame('-1.2', (new FloatNode(-1.2))->accept(new ASCIIPrinter()));
        static::assertSame('99.5', (new FloatNode(99.5))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('0', (new FloatNode(0))->accept(new LaTeXPrinter()));
        static::assertSame('1.2', (new FloatNode(1.2))->accept(new LaTeXPrinter()));
        static::assertSame('-1.2', (new FloatNode(-1.2))->accept(new LaTeXPrinter()));
        static::assertSame('99.5', (new FloatNode(99.5))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('0:float', (new FloatNode(0))->accept(new TreePrinter()));
        static::assertSame('1.2:float', (new FloatNode(1.2))->accept(new TreePrinter()));
        static::assertSame('-1.2:float', (new FloatNode(-1.2))->accept(new TreePrinter()));
        static::assertSame('99.5:float', (new FloatNode(99.5))->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNumberNodes(): void
    {
        $node  = new FloatNode(1.2);
        $other = new FloatNode(1.2);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNumberNodes(): void
    {
        $node  = new FloatNode(1);
        $other = new FloatNode(2);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new FloatNode(1);
        $other = new IntegerNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(2, (new FloatNode(0))->complexity());
        static::assertEquals(2, (new FloatNode(1.2))->complexity());
        static::assertEquals(2, (new FloatNode(-1.2))->complexity());
        static::assertEquals(2, (new FloatNode(99.5))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('0', (string)new FloatNode(0));
        static::assertEquals('1.2', (string)new FloatNode(1.2));
        static::assertEquals('-1.2', (string)new FloatNode(-1.2));
        static::assertEquals('99.5', (string)new FloatNode(99.5));
    }
}
