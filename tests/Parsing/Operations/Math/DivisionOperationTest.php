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
 * Class DivisionOperationTest
 */
class DivisionOperationTest extends TestCase
{
    private DivisionOperation $divisionOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->divisionOperation = new DivisionOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideIntegerNode(): void
    {
        $leftOperand  = new IntegerNode(10);
        $rightOperand = new IntegerNode(2);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(5.0, $division->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideRationalNode(): void
    {
        $leftOperand  = new RationalNode(1, 4);
        $rightOperand = new RationalNode(1, 2);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(0.5, $division->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideFloatNode(): void
    {
        $leftOperand  = new FloatNode(5.5);
        $rightOperand = new FloatNode(2.0);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(2.75, $division->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideNumericNodeWithZeroLeftOperand(): void
    {
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(50);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(0, $division->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddDivideNodeWithZeroRightOperand(): void
    {
        $leftOperand  = new FloatNode(50);
        $rightOperand = new FloatNode(0);
        $this->expectException(DivisionByZeroException::class);
        $this->divisionOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideNumericNodeWithRightOperandEqualOne(): void
    {
        $leftOperand  = new FloatNode(50);
        $rightOperand = new FloatNode(1);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50.0, $division->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideNonNumericNodeOperands(): void
    {
        $leftOperand  = new FunctionNode('sqrt', [new IntegerNode(16)]);
        $rightOperand = new FloatNode(2.0);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode('/', new FunctionNode('sqrt', [new IntegerNode(16)]), new FloatNode(2.0)),
            $division
        );
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanDivideEqualNonNumericNodeOperands(): void
    {
        $leftOperand  = new FunctionNode('sqrt', [new IntegerNode(16)]);
        $rightOperand = new FunctionNode('sqrt', [new IntegerNode(16)]);
        $division     = $this->divisionOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new IntegerNode(1),
            $division
        );
    }
}
