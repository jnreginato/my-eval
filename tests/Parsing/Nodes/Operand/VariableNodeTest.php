<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\SyntaxErrorException;
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
 * Class VariableNodeTest
 */
class VariableNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(new VariableNode('VARIABLE'), Node::factory(new Token('VARIABLE', TokenType::VARIABLE)));
    }

    /**
     * @return void
     */
    public function testCanEvaluate(): void
    {
        static::assertSame(100.0, (new VariableNode('VARIABLE'))->evaluate(['VARIABLE' => 100]));
    }

    /**
     * @return void
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): void
    {
        static::assertSame(100.0, (new VariableNode('VARIABLE'))->accept(new StdMathEvaluator(['VARIABLE' => 100])));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): void
    {
        static::assertEquals(
            new RationalNode(1, 2),
            (new VariableNode('VARIABLE'))->accept(new RationalEvaluator(['VARIABLE' => '1/2']))
        );
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanAcceptComplexEvaluatorVisitor(): void
    {
        static::assertEquals(
            new Complex(100, 0),
            (new VariableNode('VARIABLE'))->accept(new ComplexEvaluator(['VARIABLE' => 100]))
        );
    }

    /**
     * @return void
     */
    public function testCanAcceptDifferentiatorVisitor(): void
    {
        static::assertEquals(
            new IntegerNode(0),
            (new VariableNode('VARIABLE'))->accept(new Differentiator('x'))
        );
    }

    /**
     * @return void
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(100.0, (new VariableNode('VARIABLE'))->accept(new LogicEvaluator(['VARIABLE' => 100])));
    }

    /**
     * @return void
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame('VARIABLE', (new VariableNode('VARIABLE'))->accept(new ASCIIPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptLaTeXPrinterVisitor(): void
    {
        static::assertSame('VARIABLE', (new VariableNode('VARIABLE'))->accept(new LaTeXPrinter()));
    }

    /**
     * @return void
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame('VARIABLE', (new VariableNode('VARIABLE'))->accept(new TreePrinter()));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoEqualNumberNodes(): void
    {
        $node  = new VariableNode('VARIABLE');
        $other = new VariableNode('VARIABLE');

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNumberNodes(): void
    {
        $node  = new VariableNode('VARIABLE');
        $other = new VariableNode('VAR');

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new VariableNode('VARIABLE');
        $other = new IntegerNode(1);

        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1, (new VariableNode('VARIABLE'))->complexity());
    }

    /**
     * @return void
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals('VARIABLE', (string)new VariableNode('VARIABLE'));
    }
}
