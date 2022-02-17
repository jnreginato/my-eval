<?php

declare(strict_types=1);

namespace MyEval\Parsing\Traits;

use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;

use function is_float;
use function is_int;

/**
 * Trait for upgrading numbers (integers and floats) to NumberNode, making it possible to call the Node constructors
 * directly with numbers, making the code cleaner.
 */
trait Sanitize
{
    /**
     * Convert integers and floats to NumberNodes.
     *
     * @param Node|float|int $operand
     *
     * @return Node
     */
    protected function sanitize(mixed $operand): mixed
    {
        if (is_int($operand)) {
            return new IntegerNode($operand);
        }

        if (is_float($operand)) {
            return new FloatNode($operand);
        }

        return $operand;
    }
}
