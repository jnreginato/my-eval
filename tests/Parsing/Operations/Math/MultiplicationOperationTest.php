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
 * Class MultiplicationOperationTest
 */
class MultiplicationOperationTest extends TestCase
{
    private MultiplicationOperation $multiplicationOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->multiplicationOperation = new MultiplicationOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyIntegerNode(): void
    {
        $leftOperand    = new IntegerNode(10);
        $rightOperand   = new IntegerNode(10);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(100, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyRationalNode(): void
    {
        $leftOperand    = new RationalNode(1, 2);
        $rightOperand   = new RationalNode(1, 2);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(0.25, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyFloatNode(): void
    {
        $leftOperand    = new FloatNode(10.5);
        $rightOperand   = new FloatNode(10.1);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(106.05, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyNumericNodeWithZeroLeftOperand(): void
    {
        $leftOperand    = new IntegerNode(0);
        $rightOperand   = new IntegerNode(50);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(0, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanAddMultiplyNodeWithZeroRightOperand(): void
    {
        $leftOperand    = new FloatNode(50);
        $rightOperand   = new FloatNode(0);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(0, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyNumericNodeWithLeftOperandEqualOne(): void
    {
        $leftOperand    = new IntegerNode(1);
        $rightOperand   = new IntegerNode(50);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyNumericNodeWithRightOperandEqualOne(): void
    {
        $leftOperand    = new FloatNode(50);
        $rightOperand   = new FloatNode(1);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50.0, $multiplication->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCanMultiplyNonNumericNodeOperands(): void
    {
        $leftOperand    = new FunctionNode('sqrt', [16]);
        $rightOperand   = new FloatNode(2.0);
        $multiplication = $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode('*', new FunctionNode('sqrt', [16]), new FloatNode(2.0)),
            $multiplication
        );
    }
}
