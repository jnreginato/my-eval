<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;

/**
 * Simple string representation of an AST.
 *
 * Probably most useful for debugging purposes.
 *
 * Implementation of a Visitor, transforming an AST into a string representation of the tree.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\StdMathEval;
 * use MyEval\Solving\TreePrinter;
 *
 * $parser = new StdMathEval();
 * $tree = $parser->parse('exp(2x)+xy');
 * printer = new TreePrinter();
 * result = $tree->accept($printer);  // Generates "(+, exp((*, 2:int, x)), (*, x, y))".
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\TreePrinter;
 *
 * // Tokenize
 * $lexer = new StdMathLexer();
 * $tokens = $lexer->tokenize('exp(2x)+xy');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Evaluate
 * printer = new TreePrinter();
 * $value = $ast->accept(printer);
 * ~~~
 */
class TreePrinter implements Visitor, LogicVisitor
{
    /**
     * Print a IntegerNode.
     *
     * @param IntegerNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitIntegerNode(IntegerNode $node): string
    {
        $val = $node->value;
        return "$val:int";
    }

    /**
     * Print a RationalNode.
     *
     * @param RationalNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitRationalNode(RationalNode $node): string
    {
        $p = $node->getNumerator();
        $q = $node->getDenominator();
        return "$p/$q:rational";
    }

    /**
     * Print a NumberNode.
     *
     * @param FloatNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitNumberNode(FloatNode $node): string
    {
        $val = $node->value;
        return "$val:float";
    }

    /**
     * Print a BooleanNode.
     *
     * @param BooleanNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitBooleanNode(BooleanNode $node): string
    {
        return $node->value ? 'true:bool' : 'false:bool';
    }

    /**
     * Print a VariableNode.
     *
     * @param VariableNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitVariableNode(VariableNode $node): string
    {
        return $node->value;
    }

    /**
     * Print a ConstantNode.
     *
     * @param ConstantNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitConstantNode(ConstantNode $node): string
    {
        return $node->value;
    }

    /**
     * Print an ExpressionNode.
     *
     * @param InfixExpressionNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node): string
    {
        $leftValue = $node->getLeft()?->accept($this);
        $operator  = $node->operator;

        // The operator and the right side are optional, remember?
        if (!$operator) {
            return (string)$leftValue;
        }

        $right = $node->getRight();

        if ($right) {
            $rightValue = $right->accept($this);
            return "($operator, $leftValue, $rightValue)";
        }

        return "($operator, $leftValue)";
    }

    /**
     * Print a IfNode.
     *
     * @param TernaryExpressionNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitTernaryNode(TernaryExpressionNode $node): string
    {
        $operator   = $node->operator;
        $condiction = $node->getCondition();
        $leftValue  = $node->getLeft()?->accept($this);
        $rightValue = $node->getRight()?->accept($this);

        return "($condiction; $leftValue; $rightValue):$operator";
    }

    /**
     * Print a FunctionNode.
     *
     * @param FunctionNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitFunctionNode(FunctionNode $node): string
    {
        $functionName = $node->operator;

        $inner = [];
        foreach ($node->operand as $operand) {
            $inner[] = $operand?->accept($this);
        }
        $params = implode(', ', $inner);

        return "$functionName($params)";
    }
}
