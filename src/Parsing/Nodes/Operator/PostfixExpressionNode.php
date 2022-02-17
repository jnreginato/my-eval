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
 * AST node representing a postfix operator.
 *
 * Only for temporary use in the parser.
 * The node will be converted to a FunctionNode when consumed by the parser.
 */
class PostfixExpressionNode extends AbstractOperatorNode
{
    /**
     * @param string $operator Symbol of the postfix operator. Currently, only '!' is possible.
     *
     * @throws UnknownOperatorException
     */
    public function __construct(
        public readonly string $operator = '!'
    ) {
        if (!in_array($this->operator, ['!', '!!'])) {
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

        return $this->operator;
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
