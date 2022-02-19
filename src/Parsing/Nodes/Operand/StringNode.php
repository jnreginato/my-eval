<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Solving\Visitor;

/**
 * AST node representing a string operand.
 */
class StringNode extends OperandNode
{
    /**
     * @param string $value Name of represented string variable, e.g. '0.99'.
     */
    public function __construct(
        public readonly string $value
    ) {
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitVariableNode() function on a Visitor class.
     *
     * ## Example:
     * - evaluators: StdMathEvaluator, RationalEvaluator, ComplexEvaluator, Differentiator, LogicEvaluator or
     * - printers: ASCIIPrinter, LaTeXPrinter, TreePrinter.
     *
     * @param Visitor $visitor
     *
     * @return mixed
     */
    public function accept(Visitor $visitor): string
    {
        return $this->value;
    }
}
