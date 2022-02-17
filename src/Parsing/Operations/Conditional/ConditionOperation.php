<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Conditional;

use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\NumericNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;
use MyEval\Parsing\Traits\Numeric;
use MyEval\Parsing\Traits\Operand;
use MyEval\Parsing\Traits\Sanitize;

/**
 * Factory for creating an BooleanNode or a InfixExpressionNode representing a conditional operation.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class ConditionOperation implements ConditionOperationInterface
{
    use Sanitize;
    use Numeric;
    use Operand;

    private static Node $then;
    private static Node $else;

    /**
     * Create a Node representing a ternary expression: 'if condition, then left, else right'.
     *
     * Using some simplification rules, create a BooleanNode or a TernaryExpressionNode giving an AST correctly
     * representing a ternary if expression.
     *
     * ## Simplification rules:
     *
     * - If condition is a BooleanNode, return the proper part ('then' or 'else');
     * - If condition is a NumericNode with value different from zero, return the 'then' Node part;
     * - If condition is a NumericNode with value equal to zero, return the 'else' Node part;
     * - If condition is a relational InfixExpressionNode and the leftOperand and rightOperand of condition are the same
     *   OperandNode, use a single BooleanNode representing their result to return the proper part ('then' or 'else');
     * - Else, return the TernaryExpressionNode unchanged.
     *
     * @param Node $condition
     * @param Node $then First term
     * @param Node $else Second term
     *
     * @return Node
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function makeNode(Node $condition, Node $then, Node $else): Node
    {
        self::$then = $then;
        self::$else = $else;

        if (!$condition instanceof BooleanNode) {
            $node = $this->processCondiction($condition);
            return $node ?? new TernaryExpressionNode($condition, $then, $else);
        }

        return $this->simplify($condition);
    }

    /**
     * Process condition of TernaryNode.
     *
     * @param InfixExpressionNode $condition
     *
     * @return Node|null
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    private function processCondiction(Node $condition): ?Node
    {
        if ($condition instanceof NumericNode && (float)$condition->value !== 0.0) {
            return self::$then;
        }

        if ($condition instanceof NumericNode && (float)$condition->value === 0.0) {
            return self::$else;
        }

        if ($condition instanceof InfixExpressionNode) {
            return $this->processInfixExpressionCondiction($condition);
        }

        return null;
    }

    /**
     * Simplify node when condition is InfixExpressionNode.
     *
     * @param InfixExpressionNode $condition
     *
     * @return Node|null
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    private function processInfixExpressionCondiction(InfixExpressionNode $condition): ?Node
    {
        if (!$condition->getLeft() || !$condition->getRight()) {
            throw new SyntaxErrorException();
        }

        if (!$this->isSameOperandTerms($condition->getLeft(), $condition->getRight())) {
            return null;
        }

        $boolValue = $this->processRelation($condition->getLeft(), $condition->getRight(), $condition->operator);

        return $this->simplify(new BooleanNode($boolValue ? 'true' : 'false'));
    }

    /**
     * Simplify node when condition is boolean.
     *
     * @param BooleanNode $condition
     *
     * @return Node
     */
    private function simplify(BooleanNode $condition): Node
    {
        return $condition->value ? self::$then : self::$else;
    }
}
