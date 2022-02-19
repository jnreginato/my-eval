<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Math;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class SubtractionOperationTest
 */
class SubtractionOperationTest extends TestCase
{
    private SubtractionOperation $subtractionOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->subtractionOperation = new SubtractionOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanReturnAUnaryMinus(): void
    {
        $leftOperand = new IntegerNode(100);
        $subtraction = $this->subtractionOperation->makeNode($leftOperand, null);
        static::assertSame(-100, $subtraction->value);

        $leftOperand = new RationalNode(1, 2);
        $subtraction = $this->subtractionOperation->makeNode($leftOperand, null);
        static::assertSame(-0.5, $subtraction->value);

        $leftOperand = new FloatNode(1.2);
        $subtraction = $this->subtractionOperation->makeNode($leftOperand, null);
        static::assertSame(-1.2, $subtraction->value);

        $leftOperand = new InfixExpressionNode('-', 1, null);
        $subtraction = $this->subtractionOperation->makeNode($leftOperand, null);
        static::assertSame(1, $subtraction->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractIntegerNode(): void
    {
        $leftOperand  = new IntegerNode(101);
        $rightOperand = new IntegerNode(1);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(100, $subtraction->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractRationalNode(): void
    {
        $leftOperand  = new RationalNode(3, 2);
        $rightOperand = new RationalNode(2, 2);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(0.5, $subtraction->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractFloatNode(): void
    {
        $leftOperand  = new FloatNode(100.1);
        $rightOperand = new FloatNode(0.1);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(100.0, $subtraction->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractNumericNodeWithZeroLeftOperand(): void
    {
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(50);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(-50, $subtraction->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractNumericNodeWithZeroRightOperand(): void
    {
        $leftOperand  = new FloatNode(50);
        $rightOperand = new FloatNode(0);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50.0, $subtraction->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractNonNumericNodeOperands(): void
    {
        $leftOperand  = new FunctionNode('sqrt', [new IntegerNode(16)]);
        $rightOperand = new FloatNode(2.0);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode('-', new FunctionNode('sqrt', [new IntegerNode(16)]), new FloatNode(2.0)),
            $subtraction
        );
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanSubtractEqualNonNumericNodeOperands(): void
    {
        $leftOperand  = new FunctionNode('sqrt', [new IntegerNode(16)]);
        $rightOperand = new FunctionNode('sqrt', [new IntegerNode(16)]);
        $subtraction  = $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new IntegerNode(0),
            $subtraction
        );
    }
}
