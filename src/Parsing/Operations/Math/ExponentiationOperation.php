<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations\Math;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Extensions\Rational;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\NumericNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Traits\Numeric;
use MyEval\Parsing\Traits\Sanitize;

/**
 * Factory for creating an ExpressionNode representing '^'.
 *
 * Some basic simplification is applied to the resulting Node.
 */
class ExponentiationOperation implements MathOperationInterface
{
    use Sanitize;
    use Numeric;

    /**
     * Create a Node representing '$leftOperand^$rightOperand'.
     *
     * Using some simplification rules, create a NumericNode (IntegerNode, RationalNode or FloatNode) or an
     * InfixExpressionNode giving an AST correctly representing '$leftOperand^$rightOperand'.
     *
     * ## Simplification rules:
     *
     * - If $leftOperand and $rightOperand are both NumericNode's, return a single NumericNode containing x^y;
     * - If $rightOperand is a NumericNode representing 0, return '1';
     * - If $rightOperand is a NumericNode representing 1, return $leftOperand;
     * - If $leftOperand is already a power x=a^b and $rightOperand is a NumericNode, return a^(b*y).
     *
     * @param Node $leftOperand  First term
     * @param Node $rightOperand Second term
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     */
    public function makeNode(Node $leftOperand, Node $rightOperand): Node
    {
        // Simplification if the exponent is a number.
        if ($this->isNumeric($rightOperand)) {
            $node = $this->numericExponent($leftOperand, $rightOperand);
        } else {
            $node = $this->doubleExponentiation($leftOperand, $rightOperand);
        }

        if ($node) {
            return $node;
        }

        return new InfixExpressionNode('^', $leftOperand, $rightOperand);
    }

    /**
     * Simplify an expression x^y, when y is numeric.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return NumericNode|null
     * @throws ExponentialException
     */
    private function numericExponent(Node $leftOperand, Node $rightOperand): ?Node
    {
        // 0^0 throws an exception
        if (
            $this->isNumeric($leftOperand) &&
            $this->isNumeric($rightOperand) &&
            (float)$leftOperand->value === 0.0 &&
            (float)$rightOperand->value === 0.0
        ) {
            throw new ExponentialException();
        }

        // x^0 = 1
        if ((float)$rightOperand->value === 0.0) {
            return new IntegerNode(1);
        }

        // x^1 = x
        if ((float)$rightOperand->value === 1.0) {
            return $leftOperand;
        }

        if (!$this->isNumeric($leftOperand) || !$this->isNumeric($rightOperand)) {
            return null;
        }

        return $this->processNumericNodeType($leftOperand, $rightOperand);
    }

    /**
     * Simplify expression node when operands are numeric accordin type.
     *
     * @throws DivisionByZeroException
     */
    private function processNumericNodeType(Node $leftOperand, Node $rightOperand): ?NumericNode
    {
        // Compute x^y if both are numbers.
        switch ($this->resultingType($leftOperand, $rightOperand)) {
            case Node::NUMERIC_INTEGER:
                if ($rightOperand->value > 0) {
                    $node = new IntegerNode($leftOperand->value ** $rightOperand->value);
                }
                break;

            case Node::NUMERIC_RATIONAL:
                $node = new FloatNode((float)($leftOperand->value ** $rightOperand->value));
                $node = Rational::fromFloat($node->value);
                $node = new RationalNode($node->getNumerator(), $node->getDenominator());
                break;

            case Node::NUMERIC_FLOAT:
            default:
                $node = new FloatNode((float)($leftOperand->value ** $rightOperand->value));
                break;
        }

        return $node ?? null;
    }

    /**
     * Simplify (x^a)^b when a and b are both numeric.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return Node|null
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws ExponentialException
     */
    private function doubleExponentiation(Node $leftOperand, Node $rightOperand): ?Node
    {
        // (x^a)^b -> x^(ab) for a, b numbers
        if ($leftOperand instanceof InfixExpressionNode && $leftOperand->operator === '^') {
            $power = (new MultiplicationOperation())->makeNode($leftOperand->getRight(), $rightOperand);
            $base  = $leftOperand->getLeft();

            return $base ? $this->makeNode($base, $power) : null;
        }

        // No simplification found
        return null;
    }
}
