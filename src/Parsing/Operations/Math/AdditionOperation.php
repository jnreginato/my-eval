<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Math;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\NumericNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Traits\Numeric;
use MyEval\Parsing\Traits\Sanitize;

/**
 * Factory for creating an InfixExpressionNode representing '+'.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class AdditionOperation implements MathOperationInterface
{
    use Sanitize;
    use Numeric;

    /**
     * Create a Node representing 'leftOperand + rightOperand'.
     *
     * Using some simplification rules, create a NumericNode (IntegerNode, RationalNode or FloatNode) or an
     * InfixExpressionNode giving an AST correctly representing 'leftOperand + rightOperand'.
     *
     * ## Simplification rules:
     *
     * - If $leftOperand and $rightOperand are both NumericNode's, return a single NumericNode containing their sum;
     * - If $leftOperand or $rightOperand are NumbericNode's representing 0, return the other term unchanged.
     * - Else return an InfixExpressionNode.
     *
     * @param Node $leftOperand  First term
     * @param Node $rightOperand Second term
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function makeNode(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->numericTerms($leftOperand, $rightOperand)
            ?? new InfixExpressionNode('+', $leftOperand, $rightOperand);
    }

    /**
     * Simplify addition node when operands are numeric.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return Node|null
     * @throws DivisionByZeroException
     */
    private function numericTerms(Node $leftOperand, Node $rightOperand): ?Node
    {
        if ($this->isNumeric($leftOperand) && (float)$leftOperand->value === 0.0) {
            return $rightOperand;
        }

        if ($this->isNumeric($rightOperand) && (float)$rightOperand->value === 0.0) {
            return $leftOperand;
        }

        if (!$this->isNumeric($leftOperand) || !$this->isNumeric($rightOperand)) {
            return null;
        }

        return $this->processNumericNodeType($leftOperand, $rightOperand);
    }

    /**
     * Simplify addition node when operands are numeric accordin type.
     *
     * @throws DivisionByZeroException
     */
    private function processNumericNodeType(Node $leftOperand, Node $rightOperand): ?NumericNode
    {
        $type = $this->resultingType($leftOperand, $rightOperand);

        switch ($type) {
            case Node::NUMERIC_INTEGER:
                $node = new IntegerNode($leftOperand->value + $rightOperand->value);
                break;
            case Node::NUMERIC_RATIONAL:
                /** @var RationalNode $leftOperand */
                $leftNumerator   = $leftOperand->getNumerator();
                $leftDenominator = $leftOperand->getDenominator();

                /** @var RationalNode $rightOperand */
                $rightNumerator   = $rightOperand->getNumerator();
                $rightDenominator = $rightOperand->getDenominator();

                $p    = $leftNumerator * $rightDenominator + $leftDenominator * $rightNumerator;
                $q    = $leftDenominator * $rightDenominator;
                $node = new RationalNode($p, $q);
                break;
            case Node::NUMERIC_FLOAT:
            default:
                $node = new FloatNode(($leftOperand->value + $rightOperand->value));
                break;
        }

        return $node;
    }
}
