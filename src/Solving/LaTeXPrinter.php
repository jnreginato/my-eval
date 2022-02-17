<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;

/**
 * Create LaTeX output code for prettyprinting a mathematical expression (for example via MathJax)
 *
 * Implementation of a Visitor, transforming an AST into a string giving LaTeX code for the expression.
 *
 * The class in general does *not* generate the best possible LaTeX code, and needs more work to be used in a
 * production setting.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\StdMathEval;
 * use MyEval\Solving\LaTeXPrinter;
 *
 * $parser = new StdMathEval();
 * $ast = $parser->parse('exp(2x)+xy');
 * printer = new LaTeXPrinter();
 * result = $ast->accept($printer);  // Generates "e^{2x}+xy"
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\LaTeXPrinter;
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
 * printer = new LaTeXPrinter();
 * result = $ast->accept($printer);  // Generates "e^{2x}+xy"
 * ~~~
 *
 * Note that surrounding `$`, `$$` or `\begin{equation}..\end{equation}` has to be added manually.
 */
class LaTeXPrinter implements Visitor
{
    /**
     * @var bool Flag to determine if division should be typeset with a solidus, e.g. x/y or a fraction \frac{x}{y}.
     */
    private bool $solidus = false;

    /**
     * Generate a LaTeX output code for an IntegerNode.
     *
     * @param IntegerNode $node AST to be typeset.
     *
     * @return string
     */
    public function visitIntegerNode(IntegerNode $node): string
    {
        return (string)$node->value;
    }

    /**
     * Generate LaTeX output code for a RationalNode.
     *
     * @param RationalNode $node AST to be typeset.
     *
     * @return string
     */
    public function visitRationalNode(RationalNode $node): string
    {
        $p = $node->getNumerator();
        $q = $node->getDenominator();

        if ($q === 1) {
            return (string)$p;
        }

        if ($this->solidus) {
            return "$p/$q";
        }

        return "\\frac" . '{' . $p . '}{' . $q . '}';
    }

    /**
     * Generate LaTeX output code for a NumberNode.
     *
     * Create a string giving LaTeX code for a NumberNode. Currently, there is no special formatting of numbers.
     *
     * @param FloatNode $node AST to be typeset.
     *
     * @return string
     */
    public function visitNumberNode(FloatNode $node): string
    {
        return (string)$node->value;
    }

    /**
     * Evaluate a BooleanNode.
     *
     * @return bool
     * @throws SyntaxErrorException
     */
    public function visitBooleanNode(): bool
    {
        throw new SyntaxErrorException();
    }

    /**
     * Generate LaTeX output code for a VariableNode.
     *
     * Create a string giving LaTeX code for a VariableNode. Currently, there is no special formatting of variables.
     *
     * @param VariableNode $node AST to be typeset.
     *
     * @return string
     */
    public function visitVariableNode(VariableNode $node): string
    {
        return $node->value;
    }

    /**
     * Generate LaTeX output code for a ConstantNode.
     *
     * Create a string giving LaTeX code for a ConstantNode.
     * `pi` typesets as `\pi` and `e` simply as `e`.
     *
     * @param ConstantNode $node AST to be typeset.
     *
     * @return string
     * @throws UnknownConstantException for nodes representing other constants.
     */
    public function visitConstantNode(ConstantNode $node): string
    {
        return match ($node->value) {
            'pi'    => '\pi{}',
            'e'     => 'e',
            'i'     => 'i',
            'NAN'   => '\operatorname{NAN}',
            'INF'   => '\infty{}',
            default => throw new UnknownConstantException($node->value),
        };
    }

    /**
     * Generate LaTeX output code for an ExpressionNode.
     *
     * Create a string giving LaTeX code for an ExpressionNode `(x op y)` where `op` is `+`, `-`, `*`, `/` or `^`.
     *
     * ## Typesetting rules:
     *
     * - Adds parentheses around each operand, if needed. I.e. if their precedence lower than that of the current Node.
     *   For example, the AST `(^ (+ 1 2) 3)` generates `(1+2)^3` but `(+ (^ 1 2) 3)` generates `1^2+3` as expected.
     * - Multiplications are typeset implicitly `(* x y)` returns `xy` or using `\cdot` if the first factor is a
     *   FunctionNode or the (left operand) in the second factor is a NumberNode, so `(* x 2)` return `x \cdot 2` and
     *   `(* (sin x) x)` return `\sin x \cdot x` (but `(* x (sin x))` returns `x\sin x`).
     * - Divisions are typeset using `\frac`.
     * - Exponentiation adds braces around the power when needed.
     *
     * @param InfixExpressionNode $node AST to be typeset.
     *
     * @return string
     * @throws UnknownOperatorException
     * @throws NullOperandException
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node): string
    {
        $left     = $node->getLeft();
        $operator = $node->operator;
        $right    = $node->getRight();

        if ($left === null || ($right === null && $operator !== '-')) {
            throw new NullOperandException();
        }

        switch ($operator) {
            case '+':
                $leftValue  = $left->accept($this);
                $rightValue = $this->parenthesize($right, $node);

                return "$leftValue+$rightValue";

            case '-':
                if ($right === null) {
                    // Unary minus
                    $leftValue = $this->parenthesize($left, $node);
                    return "-$leftValue";
                }

                // Binary minus
                $leftValue  = $left->accept($this);
                $rightValue = $this->parenthesize($right, $node);
                return "$leftValue-$rightValue";

            case '*':
                $operator = '';
                if ($this->multiplicationNeedsCdot($left, $right)) {
                    $operator = '\cdot ';
                }
                $leftValue  = $this->parenthesize($left, $node);
                $rightValue = $this->parenthesize($right, $node);

                return "$leftValue$operator$rightValue";

            case '/':
                if ($this->solidus) {
                    $leftValue  = $this->parenthesize($left, $node);
                    $rightValue = $this->parenthesize($right, $node);

                    return "$leftValue$operator$rightValue";
                }

                return '\frac{' . $left->accept($this) . '}{' . $right->accept($this) . '}';

            case '^':
                $leftValue = $this->parenthesize($left, $node, '', true);

                // Typeset exponents with solidus
                $this->solidus = true;
                $result        = $leftValue . '^' . $this->bracesNeeded($right);
                $this->solidus = false;

                return $result;

            default:
                throw new UnknownOperatorException($operator);
        }
    }

    /**
     * Evaluate a TernaryNode.
     *
     * @return void
     * @throws SyntaxErrorException
     */
    public function visitTernaryNode(): void
    {
        throw new SyntaxErrorException();
    }

    /**
     * Generate LaTeX output code for a FunctionNode.
     *
     * Create a string giving LaTeX code for a functionNode.
     *
     * ## Typesetting rules:
     *
     * - `sqrt(op)` is typeset as `\sqrt{op}.
     * - `exp(op)` is either typeset as `e^{op}`, if `op` is a simple expression or as `\exp(op)` for more complicated
     *   operands.
     *
     * @param FunctionNode $node AST to be typeset.
     *
     * @return string
     */
    public function visitFunctionNode(FunctionNode $node): string
    {
        $functionName = $node->operator;

        $operand = $node->operand->accept($this);

        switch ($functionName) {
            case 'sqrt':
                return "\\$functionName{" . $node->operand->accept($this) . '}';
            case 'exp':
                $operand = $node->operand;
                if ($operand->complexity() < 10) {
                    $this->solidus = true;
                    $result        = 'e^' . $this->bracesNeeded($operand);
                    $this->solidus = false;

                    return $result;
                }
                // Operand is complex, typset using \exp instead

                return '\exp(' . $operand->accept($this) . ')';

            case 'ln':
            case 'log':
            case 'sin':
            case 'cos':
            case 'tan':
            case 'arcsin':
            case 'arccos':
            case 'arctan':
                break;

            case 'abs':
                $operand = $node->operand;

                return '\lvert ' . $operand->accept($this) . '\rvert ';

            case '!':
            case '!!':
                return $this->visitFactorialNode($node);

            default:
                $functionName = 'operatorname{' . $functionName . '}';
        }

        return "\\$functionName($operand)";
    }

    /**
     * Check if a multiplication needs an inserted \cdot or if it can be safely written with implicit multiplication.
     *
     * @param Node $left  AST of first factor.
     * @param Node $right AST of second factor.
     *
     * @return bool
     */
    private function multiplicationNeedsCdot(Node $left, Node $right): bool
    {
        if ($left instanceof FunctionNode) {
            return true;
        }

        if ($this->isNumeric($right)) {
            return true;
        }

        if ($right instanceof InfixExpressionNode && $this->isNumeric($right->getLeft())) {
            return true;
        }

        return false;
    }

    /**
     * Generate LaTeX code for factorials.
     *
     * @param FunctionNode $node AST to be typeset.
     *
     * @return string
     */
    private function visitFactorialNode(FunctionNode $node): string
    {
        $functionName = $node->operator;
        $op           = $node->operand;
        $operand      = $op->accept($this);

        // Add parentheses most of the time.
        if ($this->isNumeric($op)) {
            if ($op->value < 0) {
                $operand = "($operand)";
            }
        } elseif (!$op instanceof VariableNode && !$op instanceof ConstantNode) {
            $operand = "($operand)";
        }

        return "$operand$functionName";
    }

    /**
     *  Add parentheses to the LaTeX representation of $node if needed.
     *
     * @param Node                $node   The AST to typeset
     * @param InfixExpressionNode $cutoff A token representing the precedence of the parent node. Operands with a lower
     *                                    precedence have parentheses added.
     * @param string              $prepend
     * @param bool                $conservative
     *
     * @return string
     */
    public function parenthesize(
        Node $node,
        InfixExpressionNode $cutoff,
        string $prepend = '',
        bool $conservative = false
    ): string {
        $text = $node->accept($this);

        if ($node instanceof InfixExpressionNode) {
            // Second term is a unary minus
            if ($node->operator === '-' && $node->getRight() === null) {
                return "($text)";
            }

            if ($cutoff->operator === '-' && $node->lowerPrecedenceThan($cutoff)) {
                return "($text)";
            }
            if ($node->strictlyLowerPrecedenceThan($cutoff)) {
                return "($text)";
            }

            if ($conservative) {
                // Add parentheses more liberally for / and ^ operators,
                // so that e.g. x/(y*z) is printed correctly
                if ($cutoff->operator === '/' && $node->lowerPrecedenceThan($cutoff)) {
                    return "($text)";
                }
                if ($cutoff->operator === '^' && $node->operator === '^') {
                    return '{' . $text . '}';
                }
            }
        }

        if ($this->isNumeric($node) && $node->value < 0) {
            return "($text)";
        }

        return "$prepend$text";
    }

    /**
     * Add curly braces around the LaTex representation of $node if needed.
     *
     * Nodes representing a single ConstantNode, VariableNode or NumberNodes (0--9) are returned as-is.
     * Other Nodes get curly braces around their LaTeX code.
     *
     * @param Node $node AST to parse.
     *
     * @return string
     */
    public function bracesNeeded(Node $node): string
    {
        if ($node instanceof VariableNode || $node instanceof ConstantNode) {
            return $node->accept($this);
        }

        if ($node instanceof IntegerNode && $node->value >= 0 && $node->value <= 9) {
            return $node->accept($this);
        }

        return '{' . $node->accept($this) . '}';
    }

    /**
     * Check if Node is numeric, i.e. a NumberNode, IntegerNode or RationalNode.
     *
     * @param Node $node AST to check.
     *
     * @return bool
     */
    private function isNumeric(Node $node): bool
    {
        return ($node instanceof FloatNode || $node instanceof IntegerNode || $node instanceof RationalNode);
    }
}
