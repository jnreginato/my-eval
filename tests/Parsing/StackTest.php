<?php

declare(strict_types=1);

namespace MyEval\Parsing;

use MyEval\Parsing\Nodes\Operand\IntegerNode;
use PHPUnit\Framework\TestCase;

/**
 * Class StackTest
 */
class StackTest extends TestCase
{
    private Stack $stack;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->stack = new Stack();

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testCanPushNodeToStack(): void
    {
        $this->stack->push(new IntegerNode(1));
        static::assertSame('1', (string)$this->stack);
    }

    /**
     * @return void
     */
    public function testCanReturnTheTopElementOfStack(): void
    {
        $peekNode = new IntegerNode(100);

        $this->stack->push(new IntegerNode(1));
        $this->stack->push(new IntegerNode(10));
        $this->stack->push(new IntegerNode(50));
        $this->stack->push($peekNode);

        $initialCount = $this->stack->count();
        static::assertSame($peekNode, $this->stack->peek());
        $finalCount = $this->stack->count();
        static::assertSame($initialCount, $finalCount);
    }

    /**
     * @return void
     */
    public function testCanReturnTheTopElementOfStackAndRemoveItFromTheStack(): void
    {
        $peekNode = new IntegerNode(100);

        $this->stack->push(new IntegerNode(1));
        $this->stack->push(new IntegerNode(10));
        $this->stack->push(new IntegerNode(50));
        $this->stack->push($peekNode);

        $initialCount = $this->stack->count();
        static::assertSame($peekNode, $this->stack->pop());
        $finalCount = $this->stack->count();
        static::assertSame($initialCount - 1, $finalCount);
    }

    /**
     * @return void
     */
    public function testCanVerifyIfStackIsEmpty(): void
    {
        $this->stack->push(new IntegerNode(1));
        $this->stack->push(new IntegerNode(10));
        $this->stack->push(new IntegerNode(50));

        static::assertFalse($this->stack->isEmpty());

        $this->stack->pop();
        $this->stack->pop();
        $this->stack->pop();

        static::assertTrue($this->stack->isEmpty());
    }
}
