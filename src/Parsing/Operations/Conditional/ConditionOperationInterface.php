<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Conditional;

use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;

/**
 * Interface for construction of ConditionNode.
 */
interface ConditionOperationInterface
{
    /**
     * Factory method to create an ConditionNode with given operands and condition.
     *
     * @param InfixExpressionNode|BooleanNode $condition
     * @param Node                            $then
     * @param Node                            $else
     *
     * @return Node
     */
    public function makeNode(InfixExpressionNode|BooleanNode $condition, Node $then, Node $else): Node;
}
