<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Relational;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Traits\Operand;

/**
 * Factory for creating an InfixExpressionNode representing a relational operation.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class RelationalOperation
{
    use Operand;

    /**
     * Create a Node representing 'leftOperand {operator} rightOperand'.
     *
     * Using some simplification rules, create a BooleanNode or InfixExpressionNode giving an AST correctly representing
     * 'leftOperand {operator} rightOperand', where operator: '=', '>', '<', '<>', '>=' or '<='.
     *
     * ## Simplification rules:
     *
     * - If $leftOperand and $rightOperand are the same OperandNode, return a BooleanNode containing their result;
     * - Else return the InfixExpressionNode containing their representation.
     *
     * @param Node   $leftOperand  First term
     * @param Node   $rightOperand Second term
     * @param string $operator
     *
     * @return BooleanNode|InfixExpressionNode
     * @throws UnknownOperatorException
     */
    public function makeNode(Node $leftOperand, Node $rightOperand, string $operator): BooleanNode|InfixExpressionNode
    {
        return $this->simplify($leftOperand, $rightOperand, $operator)
            ?? new InfixExpressionNode($operator, $leftOperand, $rightOperand);
    }

    /**
     * Simplify expression node when operands are instances of the same OperandNode class.
     *
     * @param Node   $leftOperand
     * @param Node   $rightOperand
     * @param string $operator
     *
     * @return BooleanNode|null
     * @throws UnknownOperatorException
     */
    protected function simplify(Node $leftOperand, Node $rightOperand, string $operator): ?BooleanNode
    {
        if (!$this->isSameOperandTerms($leftOperand, $rightOperand)) {
            return null;
        }

        $boolValue = $this->processRelation($leftOperand, $rightOperand, $operator);

        return new BooleanNode($boolValue ? 'true' : 'false');
    }
}
