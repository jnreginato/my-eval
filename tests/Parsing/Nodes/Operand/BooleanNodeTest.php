<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownOperatorException;
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
 * Class BooleanNodeTest
 */
class BooleanNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        $token0 = new Token('true', TokenType::BOOLEAN);
        $token1 = new Token('false', TokenType::BOOLEAN);
        $token2 = new Token('TRUE', TokenType::BOOLEAN);
        $token3 = new Token('FALSE', TokenType::BOOLEAN);

        static::assertEquals(new BooleanNode('true'), Node::factory($token0));
        static::assertEquals(new BooleanNode('false'), Node::factory($token1));
        static::assertEquals(new BooleanNode('TRUE'), Node::factory($token2));
        static::assertEquals(new BooleanNode('FALSE'), Node::factory($token3));
    }

    /**
     * @return float
     */
    public function testCanEvaluate(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new BooleanNode('true'))->evaluate();
    }

    /**
     * @return float
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new BooleanNode('true'))->accept(new StdMathEvaluator());
    }

    /**
     * @return float
     */
    public function testCanAcceptRationalEvaluatorVisitor(): float
    {
        $this->expectException(UnexpectedValueException::class);
        return (new BooleanNode('true'))->accept(new RationalEvaluator());
    }

    /**
     * @return float
     */
    public function testCanAcceptComplexEvaluatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new BooleanNode('true'))->accept(new ComplexEvaluator());
    }

    /**
     * @return float
     */
    public function testCanAcceptDifferentiatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new BooleanNode('true'))->accept(new Differentiator('x'));
    }

    /**
     * @return void
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertTrue((new BooleanNode('true'))->accept(new LogicEvaluator()));
        static::assertFalse((new BooleanNode('false'))->accept(new LogicEvaluator()));
        static::assertTrue((new BooleanNode('TRUE'))->accept(new LogicEvaluator()));
        static::assertFalse((new BooleanNode('FALSE'))->accept(new LogicEvaluator()));
    }

    /**
     * @return void
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('TRUE', (new BooleanNode('true'))->accept(new ASCIIPrinter()));
        static::assertSame('FALSE', (new BooleanNode('false'))->accept(new ASCIIPrinter()));
        static::assertSame('TRUE', (new BooleanNode('TRUE'))->accept(new ASCIIPrinter()));
        static::assertSame('FALSE', (new BooleanNode('FALSE'))->accept(new ASCIIPrinter()));
    }

    /**
     * @return float
     */
    public function testCanAcceptLaTeXPrinterVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new BooleanNode('true'))->accept(new LaTeXPrinter());
    }

    /**
     * @return void
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('true:bool', (new BooleanNode('true'))->accept(new TreePrinter()));
        static::assertSame('false:bool', (new BooleanNode('false'))->accept(new TreePrinter()));
        static::assertSame('true:bool', (new BooleanNode('TRUE'))->accept(new TreePrinter()));
        static::assertSame('false:bool', (new BooleanNode('FALSE'))->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNumberNodes(): void
    {
        $node  = new BooleanNode('true');
        $other = new BooleanNode('true');

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNumberNodes(): void
    {
        $node  = new BooleanNode('true');
        $other = new BooleanNode('false');

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new BooleanNode('true');
        $other = new IntegerNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1000, (new BooleanNode('true'))->complexity());
        static::assertEquals(1000, (new BooleanNode('false'))->complexity());
        static::assertEquals(1000, (new BooleanNode('TRUE'))->complexity());
        static::assertEquals(1000, (new BooleanNode('FALSE'))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('TRUE', (string)new BooleanNode('true'));
        static::assertEquals('FALSE', (string)new BooleanNode('false'));
        static::assertEquals('TRUE', (string)new BooleanNode('TRUE'));
        static::assertEquals('FALSE', (string)new BooleanNode('FALSE'));
    }
}
