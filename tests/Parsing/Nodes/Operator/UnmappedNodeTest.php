<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Node;
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

/**
 * Class UnmappedNodeTest
 */
class UnmappedNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(new UnmappedNode('_'), Node::factory(new Token('_', TokenType::SENTINEL)));
    }

    /**
     * @return float
     */
    public function testCanEvaluate(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new UnmappedNode('_'))->evaluate();
    }

    /**
     * @return string
     * @throws SyntaxErrorException
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new UnmappedNode('_'))->accept(new StdMathEvaluator());
    }

    /**
     * @return string
     * @throws SyntaxErrorException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new UnmappedNode('_'))->accept(new RationalEvaluator());
    }

    /**
     * @return string
     * @throws SyntaxErrorException
     */
    public function testCanAcceptComplexEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new UnmappedNode('_'))->accept(new ComplexEvaluator());
    }

    /**
     * @return string
     */
    public function testCanAcceptDifferentiatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new UnmappedNode('_'))->accept(new Differentiator('x'));
    }

    /**
     * @return string
     */
    public function testCanAcceptLogicEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new UnmappedNode('_'))->accept(new LogicEvaluator());
    }

    /**
     * @return void
     * @throws SyntaxErrorException
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('_', (new UnmappedNode('_'))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     * @throws SyntaxErrorException
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('_', (new UnmappedNode('_'))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     * @throws SyntaxErrorException
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('_', (new UnmappedNode('_'))->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNodes(): void
    {
        $node  = new UnmappedNode('_');
        $other = new UnmappedNode('_');

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new UnmappedNode('_');
        $other = new UnmappedNode('`');
        static::assertFalse($node->compareTo($other));

        $node  = new UnmappedNode('_');
        $other = new IntegerNode(1);
        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1000, (new UnmappedNode('_'))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('_', (string)new UnmappedNode('_'));
    }
}
