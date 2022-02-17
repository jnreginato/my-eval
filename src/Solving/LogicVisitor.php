<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;

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
 * LogicVisitor interface.
 *
 * Implemented by LogicEvaluator interpreter.
 * The interface specifies functions for visiting and handling each Node subclass.
 */
interface LogicVisitor extends Visitor
{
    /**
     * Interface function for visiting BooleanNodes.
     *
     * @param BooleanNode $node Node to visit.
     **/
    public function visitBooleanNode(BooleanNode $node);

    /**
     * Interface function for visiting ConstantNodes.
     *
     * @param ConstantNode $node Node to visit.
     **/

    /**
     * Interface function for visiting IfNodes.
     *
     * @param TernaryExpressionNode $node Node to visit.
     **/
    public function visitTernaryNode(TernaryExpressionNode $node);
}
