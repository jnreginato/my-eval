<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Solving\Visitor;

/**
 * AST node representing a float operand number.
 */
class FloatNode extends NumericNode
{
    /**
     * @param float $value The value of the represented number.
     */
    public function __construct(
        public readonly float $value
    ) {
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitNumberNode() function on a Visitor class.
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
        return $visitor->visitNumberNode($this);
    }
}
