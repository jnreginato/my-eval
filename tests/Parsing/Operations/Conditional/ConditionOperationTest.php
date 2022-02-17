<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Conditional;

use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class ConditionOperationTest
 */
class ConditionOperationTest extends TestCase
{
    private ConditionOperation $conditionOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->conditionOperation = new ConditionOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithBooleanNodeCondition(): void
    {
        $condition    = new BooleanNode('true');
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(1);
        $resultNode   = $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);
        static::assertSame(0, $resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithNumericNonZeroNodeCondition(): void
    {
        $condition    = new IntegerNode(1);
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(1);
        $resultNode   = $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);
        static::assertSame(0, $resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithNumericZeroNodeCondition(): void
    {
        $condition    = new IntegerNode(0);
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(1);
        $resultNode   = $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);
        static::assertSame(1, $resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithInfixExpressionNodeConditionWithoutLeft(): void
    {
        $condition    = new InfixExpressionNode('=', null, 1);
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(1);
        $this->expectException(SyntaxErrorException::class);
        $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithInfixExpressionNodeConditionWithSameOperandTerms(): void
    {
        $condition    = new InfixExpressionNode('>=', 1, 1);
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(1);
        $resultNode   = $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);
        static::assertSame(0, $resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithInfixExpressionNodeConditionWithDifferentOperandTerms(): void
    {
        $condition    = new InfixExpressionNode('<=', new IntegerNode(0), new FloatNode(0));
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new FloatNode(1);
        $resultNode   = $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);
        static::assertEquals(
            new TernaryExpressionNode(new InfixExpressionNode('<=', 0, 0.0), new IntegerNode(0), new FloatNode(1)),
            $resultNode
        );
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessWithAnotherElseNodeCondition(): void
    {
        $condition    = new TernaryExpressionNode(
            new InfixExpressionNode('<>', 1, 0),
            new IntegerNode(0),
            new FloatNode(1)
        );
        $leftOperand  = new IntegerNode(10);
        $rightOperand = new IntegerNode(11);
        $resultNode   = $this->conditionOperation->makeNode($condition, $leftOperand, $rightOperand);

        static::assertEquals(
            new TernaryExpressionNode(
                new TernaryExpressionNode(new InfixExpressionNode('<>', 1, 0), new IntegerNode(0), new FloatNode(1)),
                new IntegerNode(10),
                new IntegerNode(11)
            ),
            $resultNode
        );
    }
}
