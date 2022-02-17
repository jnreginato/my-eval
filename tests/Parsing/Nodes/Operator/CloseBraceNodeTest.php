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
 * Class CloseBraceNodeTest
 */
class CloseBraceNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(new CloseBraceNode(), Node::factory(new Token('}', TokenType::CLOSE_BRACE)));
    }

    /**
     * @return CloseBraceNode
     */
    public function testCanEmitExceptionIfUnknownOperator(): CloseBraceNode
    {
        $this->expectException(UnknownOperatorException::class);
        return new CloseBraceNode('@');
    }

    /**
     * @return float
     */
    public function testCanEvaluate(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new CloseBraceNode())->evaluate();
    }

    /**
     * @return string
     * @throws SyntaxErrorException
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new CloseBraceNode())->accept(new StdMathEvaluator());
    }

    /**
     * @return string
     * @throws SyntaxErrorException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new CloseBraceNode())->accept(new RationalEvaluator());
    }

    /**
     * @return string
     * @throws SyntaxErrorException
     */
    public function testCanAcceptComplexEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new CloseBraceNode())->accept(new ComplexEvaluator());
    }

    /**
     * @return string
     */
    public function testCanAcceptDifferentiatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new CloseBraceNode())->accept(new Differentiator('x'));
    }

    /**
     * @return string
     */
    public function testCanAcceptLogicEvaluatorVisitor(): string
    {
        $this->expectException(SyntaxErrorException::class);
        return (new CloseBraceNode())->accept(new LogicEvaluator());
    }

    /**
     * @return void
     * @throws SyntaxErrorException
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('}', (new CloseBraceNode('}'))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     * @throws SyntaxErrorException
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('}', (new CloseBraceNode())->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     * @throws SyntaxErrorException
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('}', (new CloseBraceNode())->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNodes(): void
    {
        $node  = new CloseBraceNode('}');
        $other = new CloseBraceNode();

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new CloseBraceNode();
        $other = new IntegerNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1000, (new CloseBraceNode())->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('}', (string)new CloseBraceNode());
    }
}
