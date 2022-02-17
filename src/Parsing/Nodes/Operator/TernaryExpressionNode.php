<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Solving\Visitor;

/**
 * AST node representing a ternary if operation.
 */
class TernaryExpressionNode extends AbstractExpressionNode
{
    /**
     * The operator sign.
     * Always 'if'.
     */
    public readonly string $operator;

    /**
     * @param mixed $condition The condition node.
     * @param mixed $left      The if node.
     * @param mixed $right     The else node.
     */
    public function __construct(
        private mixed $condition = null,
        mixed $left = null,
        mixed $right = null
    ) {
        $this->operator = 'if';
        $this->left     = $this->sanitize($left);
        $this->right    = $this->sanitize($right);
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitTernaryNode() function on a Visitor class.
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
        return $visitor->visitTernaryNode($this);
    }

    /**
     * Get the condiction part of the expression.
     *
     * @return Node|null
     */
    public function getCondition(): ?Node
    {
        return $this->condition;
    }

    /**
     * Set the condiction part of the expression.
     *
     * @param Node $condition
     */
    public function setCondition(Node $condition): void
    {
        $this->condition = $condition;
    }
}
