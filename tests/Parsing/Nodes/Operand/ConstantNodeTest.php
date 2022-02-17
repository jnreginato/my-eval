<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

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
 * Class ConstantNodeTest
 */
class ConstantNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(new ConstantNode('pi'), Node::factory(new Token('pi', TokenType::CONSTANT)));
    }

    /**
     * @return void
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(M_PI, (new ConstantNode('pi'))->evaluate());
    }

    /**
     * @return void
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(M_PI, (new ConstantNode('pi'))->accept(new StdMathEvaluator()));
    }

    /**
     * @return mixed
     */
    public function testCanAcceptRationalEvaluatorVisitor(): mixed
    {
        $this->expectException(UnexpectedValueException::class);
        return (new ConstantNode('pi'))->accept(new RationalEvaluator());
    }

    /**
     * @return void
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(
            new Complex(M_PI, 0),
            (new ConstantNode('pi'))->accept(new ComplexEvaluator())
        );
    }

    /**
     * @return void
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(
            new IntegerNode(0),
            (new ConstantNode('pi'))->accept(new Differentiator('x'))
        );
    }

    /**
     * @return void
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(M_PI, (new ConstantNode('pi'))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('pi', (new ConstantNode('pi'))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('\pi{}', (new ConstantNode('pi'))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('pi', (new ConstantNode('pi'))->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNumberNodes(): void
    {
        $node  = new ConstantNode('pi');
        $other = new ConstantNode('pi');

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNumberNodes(): void
    {
        $node  = new ConstantNode('pi');
        $other = new ConstantNode('e');

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new ConstantNode('pi');
        $other = new IntegerNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1, (new ConstantNode('pi'))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('pi', (string)new ConstantNode('pi'));
    }
}
