<?php

declare(strict_types=1);

namespace MyEval\Parsing;

use MyEval\Parsing\Nodes\Node;

use function count;

/**
 * Utility class, implementing a simple FIFO stack.
 */
class Stack
{
    /**
     * @var Node[] $data Internal storage of data on the stack.
     */
    protected array $data = [];

    /**
     * Push an element onto the stack.
     *
     * @param Node $element
     */
    public function push(Node $element): void
    {
        $this->data[] = $element;
    }

    /**
     * Return the top element (without popping it).
     *
     * @return bool|Node
     */
    public function peek(): bool|Node
    {
        return end($this->data);
    }

    /**
     * Return the top element and remove it from the stack.
     *
     * @return Node|null
     */
    public function pop(): ?Node
    {
        return array_pop($this->data);
    }

    /**
     * Return the current number of elements in the stack.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Returns true if the stack is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Converting the operator or the operand to a printable string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode(' ', $this->data);
    }
}
