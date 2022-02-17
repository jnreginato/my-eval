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
 * Class AdditionOperationTest
 */
class AdditionOperationTest extends TestCase
{
    private AdditionOperation $additionOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->additionOperation = new AdditionOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddIntegerNode(): void
    {
        $leftOperand  = new IntegerNode(49);
        $rightOperand = new IntegerNode(51);
        $addition     = $this->additionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(100, $addition->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddRationalNode(): void
    {
        $leftOperand  = new RationalNode(1, 2);
        $rightOperand = new RationalNode(1, 2);
        $addition     = $this->additionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(1.0, $addition->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddFloatNode(): void
    {
        $leftOperand  = new FloatNode(49.9);
        $rightOperand = new FloatNode(50.1);
        $addition     = $this->additionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(100.0, $addition->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddNumericNodeWithZeroLeftOperand(): void
    {
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(50);
        $addition     = $this->additionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50, $addition->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddNumericNodeWithZeroRightOperand(): void
    {
        $leftOperand  = new FloatNode(50);
        $rightOperand = new FloatNode(0);
        $addition     = $this->additionOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50.0, $addition->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddNonNumericNodeOperands(): void
    {
        $leftOperand  = new FunctionNode('sqrt', 16);
        $rightOperand = new FloatNode(2.0);
        $addition     = $this->additionOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode('+', new FunctionNode('sqrt', 16), new FloatNode(2.0)),
            $addition
        );
    }
}
