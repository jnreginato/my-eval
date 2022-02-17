<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;

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
 * StdMathVisitor interface.
 *
 * Implemented by every interpreter.
 * The interface specifies functions for visiting and handling each Node subclass.
 */
interface StdMathVisitor
{
    /**
     * Interface function for visiting IntegerNodes.
     *
     * @param IntegerNode $node Node to visit.
     */
    public function visitIntegerNode(IntegerNode $node);

    /**
     * Interface function for visiting RationalNodes.
     *
     * @param RationalNode $node Node to visit.
     */
    public function visitRationalNode(RationalNode $node);

    /**
     * Interface function for visiting NumberNodes.
     *
     * @param FloatNode $node Node to visit.
     */
    public function visitNumberNode(FloatNode $node);

    /**
     * Interface function for visiting VariableNodes.
     *
     * @param VariableNode $node Node to visit.
     */
    public function visitVariableNode(VariableNode $node);

    /**
     * Interface function for visiting ConstantNodes.
     *
     * @param ConstantNode $node Node to visit.
     */
    public function visitConstantNode(ConstantNode $node);

    /**
     * Interface function for visiting ExpressionNodes.
     *
     * @param InfixExpressionNode $node Node to visit.
     */
    public function visitExpressionNode(InfixExpressionNode $node);

    /**
     * Interface function for visiting FunctionNodes.
     *
     * @param FunctionNode $node Node to visit.
     */
    public function visitFunctionNode(FunctionNode $node);
}
