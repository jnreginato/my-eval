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
 * Factory for creating an InfixExpressionNode representing '-'.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class SubtractionOperation implements MathOperationInterface
{
    use Sanitize;
    use Numeric;

    /**
     * Create a Node representing 'leftOperand - rightOperand'
     *
     * Using some simplification rules, create a NumericNode (IntegerNode, RationalNode or FloatNode) or an
     * InfixExpressionNode giving an AST correctly representing 'leftOperand - rightOperand'.
     *
     * ## Simplification rules:
     *
     * - If $rightOperand is null, return a unary minus node '-x' instead.
     * - If $leftOperand and $rightOperand are both NumbericNode's, return a single NumericNode's containing their diff.
     * - If $rightOperand is a NumericNode representing 0, return $leftOperand unchanged.
     * - If $leftOperand and $rightOperand are equal, return '0'.
     * - Else return an InfixExpressionNode.
     *
     * @param Node      $leftOperand  First term
     * @param Node|null $rightOperand Second term
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function makeNode(Node $leftOperand, ?Node $rightOperand): Node
    {
        if ($rightOperand === null) {
            return $this->createUnaryMinusNode($leftOperand);
        }

        $node = $this->numericTerms($leftOperand, $rightOperand);
        if ($node) {
            return $node;
        }

        if ($leftOperand->compareTo($rightOperand)) {
            return new IntegerNode(0);
        }

        return new InfixExpressionNode('-', $leftOperand, $rightOperand);
    }

    /**
     * Simplify subtraction nodes for numeric operands.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return Node|null
     * @throws DivisionByZeroException
     */
    protected function numericTerms(Node $leftOperand, Node $rightOperand): ?Node
    {
        if ($this->isNumeric($rightOperand) && (float)$rightOperand->value === 0.0) {
            return $leftOperand;
        }

        if (!$this->isNumeric($leftOperand) || !$this->isNumeric($rightOperand)) {
            return null;
        }

        return $this->processNumericNodeType($leftOperand, $rightOperand);
    }

    /**
     * Simplify subtraction node when operands are numeric accordin type.
     *
     * @throws DivisionByZeroException
     */
    private function processNumericNodeType(Node $leftOperand, Node $rightOperand): ?NumericNode
    {
        $type = $this->resultingType($leftOperand, $rightOperand);

        switch ($type) {
            case Node::NUMERIC_INTEGER:
                $node = new IntegerNode($leftOperand->value - $rightOperand->value);
                break;
            case Node::NUMERIC_RATIONAL:
                $lNum = $leftOperand->getNumerator();
                $lDen = $leftOperand->getDenominator();
                $rNum = $rightOperand->getNumerator();
                $rDen = $rightOperand->getDenominator();
                $p    = $lNum * $rDen - $lDen * $rNum;
                $q    = $lDen * $rDen;
                $node = new RationalNode($p, $q);
                break;
            case Node::NUMERIC_FLOAT:
            default:
                $node = new FloatNode($leftOperand->value - $rightOperand->value);
                break;
        }

        return $node;
    }

    /**
     * Create a Node representing '-$operand'
     *
     * Using some simplification rules, create a NumericNode (IntegerNode, RationalNode or FloatNode) or
     * InfixExpressionNode giving an AST correctly representing '-$operand'.
     *
     * ## Simplification rules:
     *
     * - If $operand is a NumericNode, return a single NumericNode containing its negative.
     * - If $operand already is a unary minus, 'x = -y', return y
     *
     * @param Node $operand Operand
     *
     * @return Node
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function createUnaryMinusNode(Node $operand)
    {
        if ($operand instanceof IntegerNode) {
            return new IntegerNode(-$operand->value);
        }

        if ($operand instanceof RationalNode) {
            return new RationalNode(-$operand->getNumerator(), $operand->getDenominator());
        }

        if ($operand instanceof FloatNode) {
            return new FloatNode(-$operand->value);
        }

        // --x => x
        if ($operand instanceof InfixExpressionNode && $operand->operator === '-' && $operand->getRight() === null) {
            return $operand->getLeft();
        }

        return new InfixExpressionNode('-', $operand, null);
    }
}
