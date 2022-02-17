<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Logical;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;

/**
 * Factory for creating an InfixExpressionNode representing a Logical Conjunction operation.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class ConjunctionOperation extends LogicalOperation
{
    /**
     * Create a Node representing 'leftOperand {operator} rightOperand'.
     *
     * Using some simplification rules, create a BooleanNode or InfixExpressionNode giving an AST correctly representing
     * 'leftOperand && rightOperand'.
     *
     * ## Simplification rules:
     *
     * - If $leftOperand and $rightOperand are both BooleanNode, return a single BooleanNode containing their result;
     * - Else return the InfixExpressionNode.
     *
     * @param Node $leftOperand  First term
     * @param Node $rightOperand Second term
     *
     * @return BooleanNode|InfixExpressionNode
     * @throws UnknownOperatorException
     */
    public function makeNode(Node $leftOperand, Node $rightOperand): BooleanNode|InfixExpressionNode
    {
        $this->simplify($leftOperand, $rightOperand);

        $node = $this->booleanTerms();
        if ($node) {
            return $node;
        }

        return new InfixExpressionNode('&&', self::$left, self::$right);
    }

    /**
     * Simplify node when operands are booleans.
     *
     * @return BooleanNode|null
     */
    protected function booleanTerms(): ?BooleanNode
    {
        if (!self::$left instanceof BooleanNode || !self::$right instanceof BooleanNode) {
            return null;
        }

        return new BooleanNode(self::$left->value && self::$right->value ? 'true' : 'false');
    }
}
