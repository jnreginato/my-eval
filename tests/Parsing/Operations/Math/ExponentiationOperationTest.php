<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Math;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class ExponentiationOperationTest
 */
class ExponentiationOperationTest extends TestCase
{
    private ExponentiationOperation $exponentiationOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->exponentiationOperation = new ExponentiationOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationIntegerNodeExpoent(): void
    {
        $leftOperand    = new IntegerNode(2);
        $rightOperand   = new IntegerNode(3);
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(8, $exponentiation->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationLowerThanZeroIntegerNodeExpoent(): void
    {
        $leftOperand    = new IntegerNode(2);
        $rightOperand   = new IntegerNode(-3);
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(new InfixExpressionNode('^', new IntegerNode(2), new IntegerNode(-3)), $exponentiation);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationFloatNode(): void
    {
        $leftOperand    = new FloatNode(2.0);
        $rightOperand   = new FloatNode(3.0);
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(8.0, $exponentiation->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationNumericNodeWithZeroOperands(): void
    {
        // 0^0 throws an exception
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(0);
        $this->expectException(ExponentialException::class);
        $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationNumericNodeWithZeroRightOperand(): void
    {
        $leftOperand    = new FloatNode(50);
        $rightOperand   = new FloatNode(0);
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(1, $exponentiation->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationNumericNodeWithRightOperandEqualOne(): void
    {
        $leftOperand    = new FloatNode(50);
        $rightOperand   = new FloatNode(1);
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertSame(50.0, $exponentiation->value);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationWithExponentiationRightNodeOperand(): void
    {
        $leftOperand    = new InfixExpressionNode('^', new IntegerNode(2), new IntegerNode(3));
        $rightOperand   = new VariableNode('x');
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode(
                '^',
                new IntegerNode(2),
                new InfixExpressionNode(
                    '*',
                    new IntegerNode(3),
                    new VariableNode('x')
                )
            ),
            $exponentiation
        );
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationWithNonNumericRightNodeOperand(): void
    {
        $leftOperand    = new IntegerNode(2);
        $rightOperand   = new VariableNode('x');
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode(
                '^',
                new IntegerNode(2),
                new VariableNode('x')
            ),
            $exponentiation
        );
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     */
    public function testCanExponentiationNonNumericNodeOperands(): void
    {
        $leftOperand    = new FunctionNode('sqrt', 16);
        $rightOperand   = new FloatNode(2.0);
        $exponentiation = $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode('^', new FunctionNode('sqrt', 16), new FloatNode(2.0)),
            $exponentiation
        );
    }
}
