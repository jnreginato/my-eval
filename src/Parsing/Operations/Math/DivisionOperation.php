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
 * Factory for creating an ExpressionNode representing '/'.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class DivisionOperation implements MathOperationInterface
{
    use Sanitize;
    use Numeric;

    /**
     * Create a Node representing '$leftOperand/$rightOperand'
     *
     * Using some simplification rules, create a NumericNode (IntegerNode, RationalNode or FloatNode) or an
     * InfixExpressionNode giving an AST correctly representing '$leftOperand/$rightOperand'.
     *
     * ## Simplification rules:
     *
     * - If $leftOperand is a NumericNode representing 0, return 0;
     * - If $rightOperand is a NumericNode representing 1, return $leftOperand;
     * - If $leftOperand and $rightOperand are equal, return '1'.
     *
     * @param Node $leftOperand  Numerator
     * @param Node $rightOperand Denominator
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function makeNode(Node $leftOperand, Node $rightOperand): Node
    {
        // Return rational number?
        // if ($leftOperand instanceof NumberNode && $rightOperand instanceof NumberNode)
        //    return new NumberNode($leftOperand->value / $rightOperand->value);

        $node = $this->numericFactors($leftOperand, $rightOperand);
        if ($node) {
            return $node;
        }

        if ($leftOperand->compareTo($rightOperand)) {
            return new IntegerNode(1);
        }

        return new InfixExpressionNode('/', $leftOperand, $rightOperand);
    }

    /**
     * Simplify division nodes when factors are numeric.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return Node|null
     * @throws DivisionByZeroException
     */
    protected function numericFactors(Node $leftOperand, Node $rightOperand): ?Node
    {
        if ($this->isNumeric($rightOperand) && (float)$rightOperand->value === 0.0) {
            throw new DivisionByZeroException();
        }

        if ($this->isNumeric($leftOperand) && (float)$leftOperand->value === 0.0) {
            return new IntegerNode(0);
        }

        if ($this->isNumeric($rightOperand) && (float)$rightOperand->value === 1.0) {
            return $leftOperand;
        }

        if (!$this->isNumeric($leftOperand) || !$this->isNumeric($rightOperand)) {
            return null;
        }

        return $this->processNumericNodeType($leftOperand, $rightOperand);
    }

    /**
     * Simplify division node when operands are numeric accordin type.
     *
     * @throws DivisionByZeroException
     */
    private function processNumericNodeType(Node $leftOperand, Node $rightOperand): ?NumericNode
    {
        $type = $this->resultingType($leftOperand, $rightOperand);

        switch ($type) {
            case Node::NUMERIC_INTEGER:
            case Node::NUMERIC_RATIONAL:
                $p    = $leftOperand->getNumerator() * $rightOperand->getDenominator();
                $q    = $leftOperand->getDenominator() * $rightOperand->getNumerator();
                $node = new RationalNode($p, $q);
                break;

            case Node::NUMERIC_FLOAT:
            default:
                $node = new FloatNode((float)($leftOperand->value / $rightOperand->value));
                break;
        }

        return $node;
    }
}
