<?php

declare(strict_types=1);

namespace MyEval\Parsing\Traits;

use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;

use function get_class;

/**
 * Trait for upgrading numbers (integers and floats) to NumberNode, making it possible to call the Node constructors
 * directly with numbers, making the code cleaner.
 */
trait Numeric
{
    /**
     * @param $operand
     *
     * @return bool
     */
    protected function isNumeric($operand): bool
    {
        return ($operand instanceof IntegerNode || $operand instanceof RationalNode || $operand instanceof FloatNode);
    }

    /**
     * @param Node $node
     *
     * @return int
     */
    protected function orderType(Node $node): int
    {
        return match (get_class($node)) {
            IntegerNode::class  => Node::NUMERIC_INTEGER,
            RationalNode::class => Node::NUMERIC_RATIONAL,
            FloatNode::class    => Node::NUMERIC_FLOAT,
        };
    }

    /**
     * @param Node $node
     * @param Node $other
     *
     * @return int
     */
    protected function resultingType(Node $node, Node $other): int
    {
        return max($this->orderType($node), $this->orderType($other));
    }
}
