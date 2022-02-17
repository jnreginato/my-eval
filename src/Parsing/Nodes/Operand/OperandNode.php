<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Parsing\Nodes\Node;

/**
 * Abstract base class for operand nodes.
 */
abstract class OperandNode extends Node
{
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
        if (!($other instanceof static)) {
            return false;
        }

        /** @var OperandNode $other */
        return $this->value === $other->value;
    }
}
