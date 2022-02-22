<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;

/**
 * Evaluate a logical parsed expression.
 *
 * Implementation of a Visitor, transforming an AST into a floating point number, giving the *value* of the expression
 * represented by the AST.
 *
 * The class implements evaluation of all arithmetic operators as well as every elementary function and predefined
 * constant recognized by StdMathLexer and LogicLexer.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\LogicEval;
 *
 * $evaluator = new LogicEval();
 * $result = $evaluator->evaluate('if (x > y) { 9; } else { 8; }', [ 'x' => 5, 'y' => 4 ]);  // Results 9.
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\LogicLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\LogicEvaluator;
 *
 * // Tokenize
 * $lexer = new LogicLexer();
 * $tokens = $lexer->tokenize('if (x > y) { 9; } else { 8; }');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Evaluate
 * $evaluator = new LogicEvaluator([ 'x' => 5, 'y' => 4 ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class LogicEvaluator extends StdMathEvaluator implements LogicVisitor
{
    /**
     * Evaluate a BooleanNode.
     *
     * @param BooleanNode $node AST to be evaluated.
     *
     * @return bool
     */
    public function visitBooleanNode(BooleanNode $node): bool
    {
        return $node->value;
    }

    /**
     * Evaluate an Logical ExpressionNode.
     *
     * Computes the value of an ExpressionNode `x op y` where `op` is one of `=`, `>`, `<`, `<>`, `>=`, `<=`,
     * `&&`, `||`, `AND` or `OR`.
     *
     * @param InfixExpressionNode $node AST to be evaluated.
     *
     * @return bool
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function visitLogicalExpressionNode(InfixExpressionNode $node): bool
    {
        $left     = $node->getLeft();
        $operator = $node->operator;
        $right    = $node->getRight();

        if ($left === null || ($right === null)) {
            throw new NullOperandException();
        }

        $rightValue = $left->accept($this);
        $leftValue  = $right->accept($this);

        // Perform the right operation based on the operator
        return match ($operator) {
            '='         => $rightValue == $leftValue,
            '<>'        => $rightValue != $leftValue,
            '>'         => $rightValue > $leftValue,
            '<'         => $rightValue < $leftValue,
            '>='        => $rightValue >= $leftValue,
            '<='        => $rightValue <= $leftValue,
            '&&', 'AND' => $rightValue && $leftValue,
            '||', 'OR'  => $rightValue || $leftValue,
            default     => throw new UnknownOperatorException($operator),
        };
    }

    /**
     * Evaluate an visitTernaryNode.
     *
     * Computes the value of an visitTernaryNode `x op y` where `op` is one of `=`, `>`, `<`, `<>`, `<=`or `<=`.
     *
     * @param TernaryExpressionNode $node AST to be evaluated.
     *
     * @return float
     * @throws NullOperandException
     * @throws UnknownOperatorException
     */
    public function visitTernaryNode(TernaryExpressionNode $node): float
    {
        /** @var InfixExpressionNode|BooleanNode $condition */
        $condition = $node->getCondition();

        $isBooleanNode = $condition instanceof BooleanNode;

        $left  = $isBooleanNode ? $node->getLeft() : $condition->getLeft();
        $right = $isBooleanNode ? $node->getRight() : $condition->getRight();

        if ($left === null || ($right === null)) {
            throw new NullOperandException();
        }

        $leftOperand  = $left->accept($this);
        $rightOperand = $right->accept($this);
        $operator     = $isBooleanNode ? $node->operator : $condition->operator;

        if ($isBooleanNode) {
            return $condition->value ? $leftOperand : $rightOperand;
        }

        $boolValue = match ($operator) {
            '='         => $leftOperand == $rightOperand,
            '<>'        => $leftOperand != $rightOperand,
            '>'         => $leftOperand > $rightOperand,
            '<'         => $leftOperand < $rightOperand,
            '>='        => $leftOperand >= $rightOperand,
            '<='        => $leftOperand <= $rightOperand,
            '&&', 'AND' => $leftOperand && $rightOperand,
            '||', 'OR'  => $leftOperand || $rightOperand,
            default     => throw new UnknownOperatorException($operator),
        };

        $leftValue  = (float)$node->getLeft()?->accept($this);
        $rightValue = (float)$node->getRight()?->accept($this);

        return $boolValue ? $leftValue : $rightValue;
    }
}
