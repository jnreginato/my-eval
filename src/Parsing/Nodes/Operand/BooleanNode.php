<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Solving\Visitor;

use function in_array;

/**
 * AST node representing a boolean operand (true or false).
 */
class BooleanNode extends OperandNode
{
    /**
     * @var bool $value The value of the represented operand.
     */
    public readonly bool $value;

    /**
     * @param string $value The value of the represented operand.
     */
    public function __construct(string $value)
    {
        $this->value = !in_array($value, ['false', 'FALSE']);
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitBooleanNode() function on a Visitor class.
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
        return $visitor->visitBooleanNode($this);
    }
}
