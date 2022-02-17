<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Logical;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Operations\Relational\RelationalOperation;

/**
 * Factory for creating an InfixExpressionNode representing a logical operation.
 *
 * Some basic simplification is applied to the resulting Node.
 */
abstract class LogicalOperation
{
    protected static Node $left;

    protected static Node $right;

    /**
     * Create a Node representing 'leftOperand {operator} rightOperand'.
     *
     * Using some simplification rules, create a BoolenNode or InfixExpressionNode giving an AST correctly representing
     * 'leftOperand {operator} rightOperand', where operator: '&&' or '||'.
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
    abstract public function makeNode(Node $leftOperand, Node $rightOperand): BooleanNode|InfixExpressionNode;

    /**
     * @return BooleanNode|null
     */
    abstract protected function booleanTerms(): ?BooleanNode;

    /**
     * @throws UnknownOperatorException
     */
    protected function simplify(Node $leftOperand, Node $rightOperand): void
    {
        $relationalOperation = new RelationalOperation();

        self::$left  = $leftOperand;
        self::$right = $rightOperand;

        if ($leftOperand instanceof InfixExpressionNode) {
            self::$left = $relationalOperation->makeNode(
                $leftOperand->getLeft(),
                $leftOperand->getRight(),
                $leftOperand->operator
            );
        }

        if ($rightOperand instanceof InfixExpressionNode) {
            self::$right = $relationalOperation->makeNode(
                $rightOperand->getLeft(),
                $rightOperand->getRight(),
                $rightOperand->operator
            );
        }
    }
}
