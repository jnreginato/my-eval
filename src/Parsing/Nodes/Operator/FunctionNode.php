<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\OperandNode;
use MyEval\Parsing\Traits\Sanitize;
use MyEval\Solving\Visitor;

/**
 * AST node representing a function applications (e.g. sin(...)).
 */
class FunctionNode extends AbstractOperatorNode
{
    use Sanitize;

    /**
     * AST of function operand.
     */
    public ?Node $operand;

    /**
     * @param string $operator Function name, e.g. 'sin'.
     * @param mixed  $operand  AST of function operand.
     */
    public function __construct(
        public readonly string $operator,
        mixed $operand = null
    ) {
        if (is_numeric($operand)) {
            $operand = new FloatNode((float)$operand);
        }

        $this->operand = $operand;
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitFunctionNode() function on a Visitor class.
     * i.e.:
     * - evaluators: StdMathEvaluator, RationalEvaluator, ComplexEvaluator, Differentiator, LogicEvaluator or
     * - printers: ASCIIPrinter, LaTeXPrinter, TreePrinter.
     *
     * @param Visitor $visitor
     *
     * @return mixed
     */
    public function accept(Visitor $visitor): mixed
    {
        return $visitor->visitFunctionNode($this);
    }

    /**
     * Helper function, comparing two ASTs.
     *
     * Useful for testing and also for some AST transformers.
     *
     * @param Node $other Compare to this tree.
     *
     * @return bool
     */
    public function compareTo(Node $other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        return $this->operator === $other->operator && $this->operand->compareTo($other->operand);
    }

    /**
     * Configure the node operand.
     *
     * @param int|float|OperandNode $operand
     *
     * @return void
     */
    public function setOperand(int|float|Node $operand): void
    {
        $this->operand = $this->sanitize($operand);
    }
}
