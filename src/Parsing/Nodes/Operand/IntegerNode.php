<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Parsing\Nodes\Node;
use MyEval\Solving\Visitor;

/**
 * AST node representing an integer operand number.
 */
class IntegerNode extends NumericNode
{
    /**
     * @param int $value The value of the represented number.
     */
    public function __construct(
        public readonly int $value
    ) {
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitIntegerNode() function on a Visitor class.
     *
     * ## Example:
     * - evaluators: StdMathEvaluator, RationalEvaluator, ComplexEvaluator, Differentiator, LogicEvaluator or
     * - printers: ASCIIPrinter, LaTeXPrinter, TreePrinter.
     *
     * @param Visitor $visitor
     *
     * @return mixed
     */
    public function accept(Visitor $visitor): mixed
    {
        return $visitor->visitIntegerNode($this);
    }

    /**
     * Helper function, comparing two ASTs.
     *
     * Useful for testing and also for some AST transformers.
     *
     * @param OperandNode $other Compare to this tree.
     *
     * @return bool
     */
    public function compareTo(Node $other): bool
    {
        if ($other instanceof RationalNode) {
            return $other->getDenominator() === 1 && $this->getNumerator() === $other->getNumerator();
        }

        return parent::compareTo($other);
    }

    /**
     * Return the numerator of the integer.
     *
     * @return int
     */
    public function getNumerator(): int
    {
        return $this->value;
    }

    /**
     * Return the denominator of the integer (always 1).
     *
     * @return int
     */
    public function getDenominator(): int
    {
        return 1;
    }
}
