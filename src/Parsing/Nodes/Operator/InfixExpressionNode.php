<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Solving\LogicEvaluator;
use MyEval\Solving\PricingEvaluator;
use MyEval\Solving\Visitor;

use function in_array;

/**
 * AST node representing a binary infix operator.
 */
class InfixExpressionNode extends AbstractExpressionNode
{
    public const LEFT_ASSOC  = 1;
    public const RIGHT_ASSOC = 2;

    /**
     * Operator, e.g. '+', '-', '*', '/', '^', '=', '<>', '>', '<', '>=', '<=', '&&', 'AND', '||', 'OR', '!' or 'NOT'.
     */
    public readonly string $operator;

    /**
     * Precedence. Operators with higher precedence bind harder.
     */
    public readonly int $precedence;

    /**
     * Associativity of operator.
     */
    private int $associativity;

    /**
     * Construct a binary operator node from (one or) two operands and an operator.
     *
     * For convenience, the constructor accept int or float as operands, automatically converting these to NumberNodes.
     *
     * ## Example:
     *
     * ~~~{.php}
     * $node = new InfixExpressionNode('+', 1, 2);
     * ~~~
     *
     * @param string              $operator Operator.
     * @param float|int|Node|null $left     First operand.
     * @param float|int|Node|null $right    Second operand.
     *
     * @throws UnknownOperatorException
     */
    public function __construct(string $operator, mixed $left = null, mixed $right = null)
    {
        $this->operator = $operator;
        $this->left     = $this->sanitize($left);
        $this->right    = $this->sanitize($right);

        switch ($operator) {
            case '&&':
            case '||':
            case 'AND':
            case 'OR':
            case '!':
            case 'NOT':
                $this->precedence    = 1;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '=':
            case '<>':
            case '>':
            case '<':
            case '>=':
            case '<=':
                $this->precedence    = 2;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '+':
            case '-':
            case '~':
                $this->precedence    = 3;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '*':
            case '/':
                $this->precedence    = 4;
                $this->associativity = self::LEFT_ASSOC;
                break;

            case '^':
                $this->precedence    = 5;
                $this->associativity = self::RIGHT_ASSOC;
                break;

            default:
                throw new UnknownOperatorException($operator);
        }
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitInfixExpressionNode or visitLogicalExpressionNode() function on a Visitor class.
     * i.e.:
     * - evaluators: StdMathEvaluator, RationalEvaluator, ComplexEvaluator, Differentiator, LogicEvaluator or
     * - printers: ASCIIPrinter, LaTeXPrinter, TreePrinter.
     *
     * @param Visitor $visitor
     *
     * @return mixed
     * @throws NullOperandException
     * @throws UnknownOperatorException
     */
    public function accept(Visitor $visitor): mixed
    {
        if (
            ($visitor instanceof LogicEvaluator  || $visitor instanceof PricingEvaluator) &&
            in_array($this->operator, ['=', '<>', '>', '<', '>=', '<=', '&&', 'AND', '||', 'OR'])
        ) {
            return $visitor->visitLogicalExpressionNode($this);
        }

        return $visitor->visitInfixExpressionNode($this);
    }

    /**
     * Returns true if the node can represent a unary operator, i.e. if the operator is '+' or '-'.
     *
     * @return bool
     */
    public function canBeUnary(): bool
    {
        return $this->operator === '+' || $this->operator === '-' || $this->operator === '~';
    }

    /**
     * Returns true if the current Node has lower precedence than the one we compare with.
     *
     * In case of a tie, we also consider the associativity.
     * (Left associative operators are lower precedence in this context.)
     *
     * @param bool|Node $other Node compare-to.
     *
     * @return bool
     */
    public function lowerPrecedenceThan(bool|Node $other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        if ($this->precedence < $other->precedence) {
            return true;
        }

        if ($this->precedence > $other->precedence) {
            return false;
        }

        if ($this->associativity === self::LEFT_ASSOC) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the current Node has lower strictly precedence than the one we compare with.
     *
     * @param $other
     *
     * @return bool
     */
    public function strictlyLowerPrecedenceThan($other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        if ($this->precedence < $other->precedence) {
            return true;
        }

        return false;
    }
}
