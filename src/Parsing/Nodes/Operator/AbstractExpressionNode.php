<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Traits\Sanitize;

/**
 * Abstract base class for expression nodes (Infix and Ternary).
 */
abstract class AbstractExpressionNode extends AbstractOperatorNode
{
    use Sanitize;

    /**
     * Left operand.
     */
    protected ?Node $left;

    /**
     * Right operand.
     */
    protected ?Node $right;

    /**
     * Get the left side of the expression.
     *
     * @return Node|null
     */
    public function getLeft(): ?Node
    {
        return $this->left;
    }

    /**
     * Set the left side of the expression.
     *
     * @param Node $left
     */
    public function setLeft(Node $left): void
    {
        $this->left = $left;
    }

    /**
     * Get the right side of the expression.
     *
     * @return Node|null
     */
    public function getRight(): ?Node
    {
        return $this->right;
    }

    /**
     * Set the right side of the expression.
     *
     * @param Node $right
     */
    public function setRight(Node $right): void
    {
        $this->right = $right;
    }

    /**
     * Helper function, comparing two ASTs.
     *
     * Useful for testing and also for some AST transformers.
     *
     * @param AbstractExpressionNode $other Compare to this tree.
     *
     * @return bool
     */
    public function compareTo(Node $other): bool
    {
        if (!($other instanceof static)) {
            return false;
        }

        if ($this->operator !== $other->operator) {
            return false;
        }

        if (
            $this instanceof TernaryExpressionNode &&
            $other instanceof TernaryExpressionNode &&
            !$this->getCondition()?->compareTo($other->getCondition())
        ) {
            return false;
        }

        if ($this->left === null && $this->right === null) {
            return $other->getLeft() === null && $other->getRight() === null;
        }

        if ($this->left === null) {
            return $other->getLeft() === null && $this->right->compareTo($other->getRight());
        }

        if ($this->right === null) {
            return $other->getRight() === null && $this->left->compareTo($other->getLeft());
        }

        if ($this instanceof TernaryExpressionNode && $other instanceof TernaryExpressionNode) {
            return $this->getCondition()?->compareTo($other->getCondition()) &&
                $this->left->compareTo($other->getLeft()) &&
                $this->right->compareTo($other->getRight());
        }

        return $this->left->compareTo($other->getLeft()) && $this->right->compareTo($other->getRight());
    }
}
