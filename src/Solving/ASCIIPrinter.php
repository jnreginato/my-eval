<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
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
 * Pretty-printing ASCII mathematical expression.
 *
 * Implementation of a Visitor, transforming an AST into ASCII string for the expression.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\StdMathEval;
 * use MyEval\Solving\ASCIIPrinter;
 *
 * $parser = new StdMathEval();
 * $tree = $parser->parse('exp(2x)+xy');
 * printer = new ASCIIPrinter();
 * result = $tree->accept($printer);  // Generates "exp(2*x)+x*y"
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\ASCIIPrinter;
 *
 * // Tokenize
 * $lexer = new StdMathLexer();
 * $tokens = $lexer->tokenize('exp(2x)+xy');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Print
 * $printer = new ASCIIPrinter();
 * result = $tree->accept($printer);  // Generates "exp(2*x)+x*y"
 * ~~~
 */
class ASCIIPrinter implements Visitor, LogicVisitor
{
    /**
     * Generate ASCII output code for an IntegerNode.
     *
     * @param IntegerNode $node AST to be evaluated.
     */
    public function visitIntegerNode(IntegerNode $node): string
    {
        return (string)$node->value;
    }

    /**
     * Generate ASCII output code for a RationalNode.
     *
     * @param RationalNode $node AST to be evaluated.
     */
    public function visitRationalNode(RationalNode $node): string
    {
        $p = $node->getNumerator();
        $q = $node->getDenominator();

        if ($q === 1) {
            return (string)$p;
        }

        return "$p/$q";
    }

    /**
     * Generate ASCII output code for a NumberNode.
     *
     * @param FloatNode $node AST to be evaluated.
     */
    public function visitNumberNode(FloatNode $node): string
    {
        return (string)$node->value;
    }

    /**
     * Generate ASCII output code for a BooleanNode.
     *
     * @param BooleanNode $node AST to be evaluated.
     */
    public function visitBooleanNode(BooleanNode $node): string
    {
        return $node->value ? 'TRUE' : 'FALSE';
    }

    /**
     * Generate ASCII output code for a VariableNode.
     *
     * @param VariableNode $node AST to be evaluated.
     */
    public function visitVariableNode(VariableNode $node): string
    {
        return $node->value;
    }

    /**
     * Generate ASCII output code for a ConstantNode.
     *
     * @param ConstantNode $node AST to be evaluated.
     *
     * @return string
     * @throws UnknownConstantException
     */
    public function visitConstantNode(ConstantNode $node): string
    {
        return match ($node->value) {
            'pi'    => 'pi',
            'e'     => 'e',
            'i'     => 'i',
            'NAN'   => 'NAN',
            'INF'   => 'INF',
            default => throw new UnknownConstantException($node->value),
        };
    }

    /**
     * Generate ASCII output code for an ExpressionNode.
     *
     * Create a string giving ASCII output representing an ExpressionNode `(x op y)`,
     * where `op` is one of `+`, `-`, `*`, `/`, `^`, `=`, `>`, `<`, `<>`, `>=` or `<=`.
     *
     * @param InfixExpressionNode $node AST to be evaluated.
     *
     * @return string
     * @throws UnknownOperatorException
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node): string
    {
        $left     = $node->getLeft();
        $operator = $node->operator;
        $right    = $node->getRight();

        switch ($operator) {
            case '+':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue+$rightValue";

            case '-':
            case '~':
                if ($right === null) {
                    // Unary minus
                    $leftValue = $this->parenthesize($left, $node);
                    return "-$leftValue";
                }
                // Binary minus
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue-$rightValue";

            case '*':
            case '/':
                $leftValue  = $this->parenthesize($left, $node);
                $rightValue = $this->parenthesize($right, $node, '', true);
                return "$leftValue$operator$rightValue";

            case '^':
                $leftValue  = $this->parenthesize($left, $node, '', true);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue$operator$rightValue";

            case '=':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue=$rightValue";

            case '>':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue>$rightValue";

            case '<':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue<$rightValue";

            case '<>':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue<>$rightValue";

            case '>=':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue>=$rightValue";

            case '<=':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue<=$rightValue";

            case 'AND':
            case '&&':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue AND $rightValue";

            case 'OR':
            case '||':
                $leftValue  = $left?->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue OR $rightValue";

            default:
                throw new UnknownOperatorException($operator);
        }
    }

    /**
     * Generate ASCII output code for an IfNode.
     *
     * @param TernaryExpressionNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitTernaryNode(TernaryExpressionNode $node): string
    {
        if ($node->getCondition() && $node->getLeft() && $node->getRight()) {
            return $node->operator . ' (' . $node->getCondition() . ') {'
                . $node->getLeft() . '} else {' . $node->getRight() . '}';
        }

        return $node->operator;
    }

    /**
     * Generate ASCII output code for an FunctionNode.
     *
     * @param FunctionNode $node AST to be evaluated.
     *
     * @return string
     */
    public function visitFunctionNode(FunctionNode $node): string
    {
        $functionName = $node->operator;

        if ($functionName === '!' || $functionName === '!!') {
            return $this->visitFactorialNode($node);
        }

        if ($node->operand === null) {
            return '';
        }
        $operand = $node->operand->accept($this);

        return "$functionName($operand)";
    }

    /**
     * Generate ASCII output code for a factorial FunctionNode.
     *
     * @param FunctionNode $node
     *
     * @return string
     */
    private function visitFactorialNode(FunctionNode $node): string
    {
        $functionName = $node->operator;
        $operand      = $node->operand;
        $op           = $operand->accept($this);

        // Add parentheses most of the time.
        if ($operand instanceof FloatNode || $operand instanceof IntegerNode || $operand instanceof RationalNode) {
            if ($operand->value < 0) {
                $op = "($op)";
            }
        } elseif (!$operand instanceof VariableNode && !$operand instanceof ConstantNode) {
            $op = "($op)";
        }

        return "$op$functionName";
    }

    /**
     * @param Node|null           $node
     * @param InfixExpressionNode $cutoff
     * @param string              $prepend
     * @param bool                $conservative
     *
     * @return string
     * @throws UnknownOperatorException
     */
    public function parenthesize(
        ?Node $node,
        InfixExpressionNode $cutoff,
        string $prepend = '',
        bool $conservative = false
    ): string {
        $text = $node?->accept($this);

        if ($node instanceof InfixExpressionNode) {
            // Second term is a unary minus
            if ($node->operator === '-' && $node->getRight() === null) {
                return "($text)";
            }

            if ($cutoff->operator === '-' && $node->lowerPrecedenceThan($cutoff)) {
                return "($text)";
            }

            if ($conservative) {
                // Add parentheses more liberally for / and ^ operators, so that e.g. x/(y*z) is printed correctly
                if ($cutoff->operator === '/' && $node->lowerPrecedenceThan($cutoff)) {
                    return "($text)";
                }
                if ($cutoff->operator === '^' && $node->operator === '^') {
                    return "($text)";
                }
            }

            if ($node->strictlyLowerPrecedenceThan($cutoff)) {
                return "($text)";
            }
        }

        if (
            ($node instanceof FloatNode || $node instanceof IntegerNode || $node instanceof RationalNode) &&
            $node->value < 0
        ) {
            return "($text)";
        }

        // Treat rational numbers as divisions on printing
        if ($node instanceof RationalNode && $node->getDenominator() !== 1) {
            $fakeNode = new InfixExpressionNode('/', $node->getNumerator(), $node->getDenominator());

            if ($fakeNode->lowerPrecedenceThan($cutoff)) {
                return "($text)";
            }
        }

        return "$prepend$text";
    }
}
