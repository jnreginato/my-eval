<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Parsing\Nodes\Node;
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
     * @param string $operator Function name, e.g. 'sin'.
     * @param array  $operand  array of ASTs of function operands.
     */
    public function __construct(
        public readonly string $operator,
        public array $operand = [],
        public int $paramsNumber = 1
    ) {
        $this->setParamsNumber();
    }

    /**
     * @return void
     */
    private function setParamsNumber(): void
    {
        match ($this->operator) {
            default  => $this->paramsNumber = 1,
            'ending' => $this->paramsNumber = 2,
        };
    }

    /**
     * @return int
     */
    public function getParamsNumber(): int
    {
        return $this->paramsNumber;
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

        return $this->operator === $other->operator && $this->operand[0]?->compareTo($other->operand[0]);
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
        unset($this->operand);
        $this->operand[0] = $this->sanitize($operand);
    }

    /**
     * Configure the node operand.
     *
     * @param int|float|OperandNode $operand
     *
     * @return void
     */
    public function addOperand(int|float|Node $operand): void
    {
        $this->operand[] = $this->sanitize($operand);
    }
}
