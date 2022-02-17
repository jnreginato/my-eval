<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Extensions\Complex;
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
 * Class RationalNodeTest
 */
class RationalNodeTest extends TestCase
{
    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanConstrucWithNormalize(): void
    {
        static::assertSame('1/2', (string)new RationalNode(1, 2));
        static::assertSame('1/2', (string)new RationalNode(2, 4));
        static::assertSame('1/3', (string)new RationalNode(5, 15));
        static::assertSame('1/3', (string)new RationalNode(10, 30));
        static::assertSame('50', (string)new RationalNode(100, 2));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanConstrucWithoutNormalize(): void
    {
        static::assertSame('1/2', (string)new RationalNode(1, 2, false));
        static::assertSame('2/4', (string)new RationalNode(2, 4, false));
        static::assertSame('5/15', (string)new RationalNode(5, 15, false));
        static::assertSame('10/30', (string)new RationalNode(10, 30, false));
        static::assertSame('100/2', (string)new RationalNode(100, 2, false));
    }

    /**
     * @return RationalNode
     * @throws DivisionByZeroException
     */
    public function testCanEmitAnErrorOnZeroDenominator(): RationalNode
    {
        $this->expectException(DivisionByZeroException::class);
        return new RationalNode(1, 0);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(0.0, (new RationalNode(0, 1))->evaluate());
        static::assertSame(1.0, (new RationalNode(1, 1))->evaluate());
        static::assertSame(-1.0, (new RationalNode(-1, 1))->evaluate());
        static::assertSame(100.0, (new RationalNode(100, 1))->evaluate());
        static::assertSame(50.0, (new RationalNode(100, 2))->evaluate());
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(0.0, (new RationalNode(0, 1))->accept(new StdMathEvaluator()));
        static::assertSame(5.0, (new RationalNode(10, 2))->accept(new StdMathEvaluator()));
        static::assertSame(-0.5, (new RationalNode(-1, 2))->accept(new StdMathEvaluator()));
        static::assertSame(99.0, (new RationalNode(99, 1))->accept(new StdMathEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): void
    {
        static::assertEquals(new RationalNode(0, 1), (new RationalNode(0, 1))->accept(new RationalEvaluator()));
        static::assertEquals(new RationalNode(5, 1), (new RationalNode(10, 2))->accept(new RationalEvaluator()));
        static::assertEquals(new RationalNode(-1, 2), (new RationalNode(-1, 2))->accept(new RationalEvaluator()));
        static::assertEquals(new RationalNode(99, 1), (new RationalNode(99, 1))->accept(new RationalEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(new Complex(0, 0), (new RationalNode(0, 1))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(5, 0), (new RationalNode(10, 2))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(-0.5, 0), (new RationalNode(-1, 2))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(99, 0), (new RationalNode(99, 1))->accept(new ComplexEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(new IntegerNode(0), (new RationalNode(0, 1))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new RationalNode(10, 2))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new RationalNode(-1, 2))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new RationalNode(99, 1))->accept(new Differentiator('x')));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(0.0, (new RationalNode(0, 1))->accept(new LogicEvaluator()));
        static::assertSame(5.0, (new RationalNode(10, 2))->accept(new LogicEvaluator()));
        static::assertSame(-0.5, (new RationalNode(-1, 2))->accept(new LogicEvaluator()));
        static::assertSame(99.0, (new RationalNode(99, 1))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('0', (new RationalNode(0, 1))->accept(new ASCIIPrinter()));
        static::assertSame('5', (new RationalNode(10, 2))->accept(new ASCIIPrinter()));
        static::assertSame('-1/2', (new RationalNode(-1, 2))->accept(new ASCIIPrinter()));
        static::assertSame('99/8', (new RationalNode(99, 8))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('0', (new RationalNode(0, 1))->accept(new LaTeXPrinter()));
        static::assertSame('5', (new RationalNode(10, 2))->accept(new LaTeXPrinter()));
        static::assertSame('\frac{-1}{2}', (new RationalNode(-1, 2))->accept(new LaTeXPrinter()));
        static::assertSame('\frac{99}{8}', (new RationalNode(99, 8))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('0/1:rational', (new RationalNode(0, 1))->accept(new TreePrinter()));
        static::assertSame('5/1:rational', (new RationalNode(10, 2))->accept(new TreePrinter()));
        static::assertSame('-1/2:rational', (new RationalNode(-1, 2))->accept(new TreePrinter()));
        static::assertSame('99/8:rational', (new RationalNode(99, 8))->accept(new TreePrinter()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCompareTwoRationalEqualNodes(): void
    {
        $node  = new RationalNode(1, 2);
        $other = new RationalNode(1, 2);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCompareTwoDifferentRationalNodes(): void
    {
        $node  = new RationalNode(1, 2);
        $other = new RationalNode(2, 1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCompareRationalAndIntegerEqualNodes(): void
    {
        $node  = new RationalNode(2, 1);
        $other = new IntegerNode(2);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCompareRationalAndIntegerDifferentNodes(): void
    {
        $node  = new IntegerNode(1);
        $other = new RationalNode(2, 1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new RationalNode(1, 2);
        $other = new FloatNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanRetrieveNumeratorAndDenominator(): void
    {
        $node = new RationalNode(99, 8);

        static::assertSame(99, $node->getNumerator());
        static::assertSame(8, $node->getDenominator());
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(2, (new RationalNode(0, 1))->complexity());
        static::assertEquals(2, (new RationalNode(10, 2))->complexity());
        static::assertEquals(2, (new RationalNode(-1, 2))->complexity());
        static::assertEquals(2, (new RationalNode(99, 8))->complexity());
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('0', (string)new RationalNode(0, 1));
        static::assertEquals('5', (string)new RationalNode(10, 2));
        static::assertEquals('-1/2', (string)new RationalNode(-1, 2));
        static::assertEquals('99/8', (string)new RationalNode(99, 8));
    }
}
