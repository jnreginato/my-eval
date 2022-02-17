<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
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
 * Class TernaryExpressionNodeTest
 */
class TernaryExpressionNodeTest extends TestCase
{
    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanBuildNodeFromToken(): void
    {
        static::assertEquals(
            new TernaryExpressionNode(),
            Node::factory(new Token('if', TokenType::IF))
        );
    }

    /**
     * @return float
     */
    public function testCanEvaluate(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new TernaryExpressionNode())->evaluate();
    }

    /**
     * @return float
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptStdMathEvaluatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new TernaryExpressionNode())->accept(new StdMathEvaluator());
    }

    /**
     * @return float
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptRationalEvaluatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new TernaryExpressionNode())->accept(new RationalEvaluator());
    }

    /**
     * @return float
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptComplexEvaluatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new TernaryExpressionNode())->accept(new ComplexEvaluator());
    }

    /**
     * @return float
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptDifferentiatorVisitor(): float
    {
        $this->expectException(SyntaxErrorException::class);
        return (new TernaryExpressionNode())->accept(new Differentiator('x'));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptLogicEvaluatorVisitor(): void
    {
        static::assertSame(
            2.0,
            (new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1))->accept(new LogicEvaluator())
        );

        static::assertSame(
            1.0,
            (new TernaryExpressionNode(new BooleanNode('false'), 2, 1))->accept(new LogicEvaluator())
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptASCIIPrinterVisitor(): void
    {
        static::assertSame(
            'if (2>1) {2} else {1}',
            (new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1))->accept(new ASCIIPrinter())
        );
    }

    /**
     * @return mixed
     * @throws NullOperandException
     * @throws UnknownOperatorException
     */
    public function testCanAcceptLaTeXPrinterVisitor(): mixed
    {
        $this->expectException(SyntaxErrorException::class);
        return (new TernaryExpressionNode())->accept(new LaTeXPrinter());
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function testCanAcceptTreePrinterVisitor(): void
    {
        static::assertSame(
            '(2>1; 2:int; 1:int):if',
            (new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1))->accept(new TreePrinter())
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanCompareTwoEqualNodes(): void
    {
        $node  = new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1);
        $other = new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1);

        static::assertTrue($node->compareTo($other));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanCompareTwoDifferentNodes(): void
    {
        $node  = new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1);
        $other = new TernaryExpressionNode(new InfixExpressionNode('<', 2, 1), 2, 1);
        static::assertFalse($node->compareTo($other));

        $node  = new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1);
        $other = new IntegerNode(1);
        static::assertFalse($node->compareTo($other));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanComputeComplexityOfTheAst(): void
    {
        static::assertEquals(1000, (new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1))->complexity());
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanTransformToString(): void
    {
        static::assertEquals(
            'if (2>1) {2} else {1}',
            (string)new TernaryExpressionNode(new InfixExpressionNode('>', 2, 1), 2, 1)
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanSetCondictionAndLeftAndRight(): void
    {
        $node = new TernaryExpressionNode();
        static::assertEquals(null, $node->getCondition());

        $node->setCondition(new InfixExpressionNode('>=', new VariableNode('x'), 1));
        static::assertEquals('x>=1', $node->getCondition());

        $node->setLeft(new IntegerNode(1));
        static::assertEquals('1', $node->getLeft());

        $node->setRight(new FloatNode(1.0));
        static::assertEquals('1.0', $node->getRight());
    }
}
