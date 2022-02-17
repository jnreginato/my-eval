<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Relational;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class RelationalOperationTest
 */
class RelationalOperationTest extends TestCase
{
    private RelationalOperation $relationalOperation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->relationalOperation = new RelationalOperation();

        parent::setUp();
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanCompareIntegerNode(): void
    {
        $leftOperand  = new IntegerNode(0);
        $rightOperand = new IntegerNode(1);
        $resultNode   = $this->relationalOperation->makeNode($leftOperand, $rightOperand, '=');
        static::assertFalse($resultNode->value);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanProcessWithInfixExpressionNodeConditionWithSameOperandTerms(): void
    {
        $leftOperand  = new InfixExpressionNode('>=', 1, 1);
        $rightOperand = new InfixExpressionNode('>=', 1, 1);
        $resultNode   = $this->relationalOperation->makeNode($leftOperand, $rightOperand, '=');
        static::assertEquals(
            new InfixExpressionNode('=', new InfixExpressionNode('>=', 1, 1), new InfixExpressionNode('>=', 1, 1)),
            $resultNode
        );
    }
}
