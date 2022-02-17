<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Math;

use MyEval\Parsing\Nodes\Node;

/**
 * Interface for construction of an InfixExpressionNode.
 *
 * The implementations of the interface, usually involves some simplification of the operands.
 */
interface MathOperationInterface
{
    /**
     * Create an InfixExpressionNode with given operands.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return Node
     */
    public function makeNode(Node $leftOperand, Node $rightOperand): Node;
}
