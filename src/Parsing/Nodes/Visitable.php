<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes;

use MyEval\Solving\Visitor;

/**
 * Interface required to implement the visitor design pattern.
 *
 * Two interfaces are required:
 * - *Visitable* should be implemented by classes to be visited, i.e. subclasses of Node.
 *      This interface consists of a single function `accept()`, called to visit the AST.
 * - *Visitor* should be implemented by AST transformers, and consists of one function for each subclass of Node,
 *      i.e. `visitXXXNode()`
 *
 *
 * Visitable interface.
 *
 * Part of the visitor design pattern implementation.
 * Every Node implements the Visitable interface, containing the single function accept().
 *
 * Implemented by the (abstract) Node class.
 *
 * ## Example:
 *
 * ~~~{.php}
 * $visitable = new InfixExpressionNode('+', 1, 2);
 * $visitor = new TreePrinter(); // Or any other Visitor
 * $visitable->accept($visitor);
 * ~~~
 */
interface Visitable
{
    /**
     * Single function in the Visitable interface.
     *
     * Calling accept() function on a Visitable class.
     * i.e. a Node (or subclass thereof) causes the supplied Visitor to traverse the AST.
     *
     * @param Visitor $visitor
     */
    public function accept(Visitor $visitor);
}
