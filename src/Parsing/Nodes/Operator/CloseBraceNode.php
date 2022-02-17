<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operator;

use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Solving\ASCIIPrinter;
use MyEval\Solving\LaTeXPrinter;
use MyEval\Solving\TreePrinter;
use MyEval\Solving\Visitor;

use function get_class;
use function in_array;

/**
 * AST node representing a close brace operand node.
 */
class CloseBraceNode extends AbstractOperatorNode
{
    /**
     * @param string $operator Symbol of the close brace operator.
     *
     * @throws UnknownOperatorException
     */
    public function __construct(
        public readonly string $operator = '}'
    ) {
        if ($this->operator !== '}') {
            throw new UnknownOperatorException($this->operator);
        }
    }

    /**
     * Single function in the Visitable interface.
     *
     * @param Visitor $visitor
     *
     * @return string
     * @throws SyntaxErrorException
     */
    public function accept(Visitor $visitor): string
    {
        if (!in_array(get_class($visitor), [ASCIIPrinter::class, LaTeXPrinter::class, TreePrinter::class])) {
            throw new SyntaxErrorException();
        }

        return '}';
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

        return $this->operator === $other->operator;
    }
}
