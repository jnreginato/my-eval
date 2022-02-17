<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Logical;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class ConjunctionOperationTest
 */
class ConjunctionOperationTest extends TestCase
{
    private ConjunctionOperation $conjunctionOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->conjunctionOperation = new ConjunctionOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithBooleanTrueTrueNodes(): void
    {
        $leftOperand  = new BooleanNode('true');
        $rightOperand = new BooleanNode('true');
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertTrue($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithBooleanTrueFalseNodes(): void
    {
        $leftOperand  = new BooleanNode('true');
        $rightOperand = new BooleanNode('false');
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertFalse($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithBooleanFalseFalseNodes(): void
    {
        $leftOperand  = new BooleanNode('false');
        $rightOperand = new BooleanNode('false');
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertFalse($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithNonBooleanNodesTrueTrue(): void
    {
        $leftOperand  = new InfixExpressionNode('<>', new IntegerNode(1), new IntegerNode(0));
        $rightOperand = new InfixExpressionNode('<>', new IntegerNode(0), new IntegerNode(1));
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertTrue($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithNonBooleanNodesTrueFalse(): void
    {
        $leftOperand  = new InfixExpressionNode('>', new IntegerNode(1), new IntegerNode(0));
        $rightOperand = new InfixExpressionNode('<', new IntegerNode(1), new IntegerNode(0));
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertFalse($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithNonBooleanNodesFalseFalse(): void
    {
        $leftOperand  = new InfixExpressionNode('>', new IntegerNode(0), new IntegerNode(1));
        $rightOperand = new InfixExpressionNode('<', new IntegerNode(1), new IntegerNode(0));
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertFalse($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithNonBooleanNodes(): void
    {
        $leftOperand  = new InfixExpressionNode('>', new VariableNode('x'), new IntegerNode(0));
        $rightOperand = new InfixExpressionNode('<', new IntegerNode(0), new VariableNode('x'));
        $resultNode   = $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
        static::assertEquals(
            new InfixExpressionNode(
                '&&',
                new InfixExpressionNode('>', new VariableNode('x'), new IntegerNode(0)),
                new InfixExpressionNode('<', new IntegerNode(0), new VariableNode('x'))
            ),
            $resultNode
        );
    }
}
