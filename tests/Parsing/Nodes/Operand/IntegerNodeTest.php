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

/**
 * Class IntegerNodeTest
 */
class IntegerNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuilNodeFromToken(): void
    {
        $token0 = new Token('0', TokenType::NATURAL_NUMBER);
        $token1 = new Token('1', TokenType::NATURAL_NUMBER);
        $token2 = new Token('1', TokenType::INTEGER);
        $token3 = new Token('-1', TokenType::INTEGER);

        static::assertEquals(new IntegerNode(0), Node::factory($token0));
        static::assertEquals(new IntegerNode(1), Node::factory($token1));
        static::assertEquals(new IntegerNode(1), Node::factory($token2));
        static::assertEquals(new IntegerNode(-1), Node::factory($token3));
    }

    /**
     * @return void
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(0.0, (new IntegerNode(0))->evaluate());
        static::assertSame(1.0, (new IntegerNode(1))->evaluate());
        static::assertSame(-1.0, (new IntegerNode(-1))->evaluate());
        static::assertSame(100.0, (new IntegerNode(100))->evaluate());
    }

    /**
     * @return void
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(0, (new IntegerNode(0))->accept(new StdMathEvaluator()));
        static::assertSame(1, (new IntegerNode(1))->accept(new StdMathEvaluator()));
        static::assertSame(-1, (new IntegerNode(-1))->accept(new StdMathEvaluator()));
        static::assertSame(99, (new IntegerNode(99))->accept(new StdMathEvaluator()));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): void
    {
        static::assertEquals(new RationalNode(0, 1), (new IntegerNode(0))->accept(new RationalEvaluator()));
        static::assertEquals(new RationalNode(1, 1), (new IntegerNode(1))->accept(new RationalEvaluator()));
        static::assertEquals(new RationalNode(-1, 1), (new IntegerNode(-1))->accept(new RationalEvaluator()));
        static::assertEquals(new RationalNode(99, 1), (new IntegerNode(99))->accept(new RationalEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(new Complex(0, 0), (new IntegerNode(0))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(1, 0), (new IntegerNode(1))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(-1, 0), (new IntegerNode(-1))->accept(new ComplexEvaluator()));
        static::assertEquals(new Complex(99, 0), (new IntegerNode(99))->accept(new ComplexEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(new IntegerNode(0), (new IntegerNode(0))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new IntegerNode(1))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new IntegerNode(-1))->accept(new Differentiator('x')));
        static::assertEquals(new IntegerNode(0), (new IntegerNode(99))->accept(new Differentiator('x')));
    }

    /**
     * @return void
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(0, (new IntegerNode(0))->accept(new LogicEvaluator()));
        static::assertSame(1, (new IntegerNode(1))->accept(new LogicEvaluator()));
        static::assertSame(-1, (new IntegerNode(-1))->accept(new LogicEvaluator()));
        static::assertSame(99, (new IntegerNode(99))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('0', (new IntegerNode(0))->accept(new ASCIIPrinter()));
        static::assertSame('1', (new IntegerNode(1))->accept(new ASCIIPrinter()));
        static::assertSame('-1', (new IntegerNode(-1))->accept(new ASCIIPrinter()));
        static::assertSame('99', (new IntegerNode(99))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('0', (new IntegerNode(0))->accept(new LaTeXPrinter()));
        static::assertSame('1', (new IntegerNode(1))->accept(new LaTeXPrinter()));
        static::assertSame('-1', (new IntegerNode(-1))->accept(new LaTeXPrinter()));
        static::assertSame('99', (new IntegerNode(99))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('0:int', (new IntegerNode(0))->accept(new TreePrinter()));
        static::assertSame('1:int', (new IntegerNode(1))->accept(new TreePrinter()));
        static::assertSame('-1:int', (new IntegerNode(-1))->accept(new TreePrinter()));
        static::assertSame('99:int', (new IntegerNode(99))->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualIntegerNodes(): void
    {
        $node  = new IntegerNode(1);
        $other = new IntegerNode(1);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentIntegerNodes(): void
    {
        $node  = new IntegerNode(1);
        $other = new IntegerNode(2);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCompareIntegerAndRationalEqualNodes(): void
    {
        $node  = new IntegerNode(2);
        $other = new RationalNode(2, 1);

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
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new IntegerNode(1);
        $other = new FloatNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanRetrieveNumeratorAndDenominator(): void
    {
        $node = new IntegerNode(99);

        static::assertSame(99, $node->getNumerator());
        static::assertSame(1, $node->getDenominator());
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1, (new IntegerNode(0))->complexity());
        static::assertEquals(1, (new IntegerNode(1))->complexity());
        static::assertEquals(1, (new IntegerNode(-1))->complexity());
        static::assertEquals(1, (new IntegerNode(99))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('0', (string)new IntegerNode(0));
        static::assertEquals('1', (string)new IntegerNode(1));
        static::assertEquals('-1', (string)new IntegerNode(-1));
        static::assertEquals('99', (string)new IntegerNode(99));
    }
}
