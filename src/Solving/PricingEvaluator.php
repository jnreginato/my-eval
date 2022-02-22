<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownVariableException;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\StringNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;

use function array_key_exists;
use function strlen;

/**
 * Evaluate a parsed mathematical expression.
 *
 * Implementation of a Visitor, transforming an AST into a floating point number, giving the *value* of the expression
 * represented by the AST.
 *
 * The class implements evaluation of all arithmetic operators as well as every elementary function and predefined
 * constant recognized by Lexer and StdMathParser.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\StdMathEval;
 *
 * $evaluator = new StdMathEval();
 * $result = $evaluator->evaluate('exp(2x)+xy', [ 'x' => 1, 'y' => -1 ]);  // Evaluate $asf using x=1, y=-1.
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\StdMathEvaluator;
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
 * $evaluator = new ComplexEvaluator([ 'x' => 1, 'y' => -1 ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class PricingEvaluator implements Visitor
{
    /**
     * Create an StdMathEvaluator with given variable values.
     *
     * @param array $variables Key/value pair holding current values of the variables used for evaluating.
     */
    public function __construct(private array $variables = [])
    {
    }

    /**
     * Evaluate an IntegerNode.
     *
     * @param IntegerNode $node AST to be evaluated.
     *
     * @return int
     */
    public function visitIntegerNode(IntegerNode $node): int
    {
        return $node->value;
    }

    /**
     * Evaluate a RationalNode.
     *
     * @param RationalNode $node AST to be evaluated.
     *
     * @return float
     */
    public function visitRationalNode(RationalNode $node): float
    {
        return $node->value;
    }

    /**
     * Evaluate a NumberNode.
     *
     * @param FloatNode $node AST to be evaluated.
     *
     * @return float
     */
    public function visitNumberNode(FloatNode $node): float
    {
        return $node->value;
    }

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
     * Evaluate a VariableNode.
     *
     * Returns the current value of a VariableNode, as defined by the constructor method.
     *
     * @param VariableNode $node AST to be evaluated.
     *
     * @return float
     * @throws UnknownVariableException
     */
    public function visitVariableNode(VariableNode $node): string
    {
        $name = $node->value;

        if (array_key_exists($name, $this->variables)) {
            return (string)$this->variables[$name];
        }

        throw new UnknownVariableException($name);
    }

    /**
     * Evaluate a ConstantNode.
     *
     * Returns the value of a ConstantNode recognized by StdMathLexer.
     *
     * @param ConstantNode $node AST to be evaluated.
     *
     * @return float
     * @throws UnknownConstantException
     */
    public function visitConstantNode(ConstantNode $node): float
    {
        return match ($node->value) {
            'pi'    => M_PI,
            'e'     => exp(1),
            'NAN'   => NAN,
            'INF'   => INF,
            default => throw new UnknownConstantException($node->value),
        };
    }

    /**
     * Evaluate an InfixExpressionNode.
     *
     * Computes the value of an infixExpressionNode `x op y` where `op` is one of `+`, `-`, `*`, `/` or `^`.
     *
     * @param InfixExpressionNode $node AST to be evaluated.
     *
     * @return float
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node): float
    {
        $left     = $node->getLeft();
        $operator = $node->operator;
        $right    = $node->getRight();

        if ($left === null || ($right === null && $operator !== '-')) {
            throw new NullOperandException();
        }

        // Perform the right operation based on the operator
        switch ($operator) {
            case '+':
                $result = $left->accept($this) + $right->accept($this);
                break;
            case '-':
                $result = $right === null ? -$left->accept($this) : $left->accept($this) - $right->accept($this);
                break;
            case '*':
                $result = $right->accept($this) * $left->accept($this);
                break;
            case '/':
                if ((float)$right->accept($this) === 0.0) {
                    throw new DivisionByZeroException();
                }
                $result = $left->accept($this) / $right->accept($this);
                break;
            case '^':
                // Check for base equal to M_E, to take care of PHP's strange implementation of pow,
                // where pow(M_E, x) is not necessarily equal to exp(x).
                if ($left->accept($this) === M_E) {
                    $result = exp((float)$right->accept($this));
                    break;
                }
                // 0^0 throws an exception
                if ((float)$left->accept($this) === 0.0 && (float)$right->accept($this) === 0.0) {
                    throw new ExponentialException();
                }
                $result = $left->accept($this) ** $right->accept($this);
                break;

            default:
                throw new UnknownOperatorException($operator);
        }

        return (float)$result;
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
     * Evaluate a FunctionNode.
     *
     * Computes the value of a FunctionNode `f(x)`, where f is an elementary function recognized by StdMathLexer.
     *
     * @param FunctionNode $node AST to be evaluated.
     *
     * @return float|int
     * @throws UnknownFunctionException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     */
    public function visitFunctionNode(FunctionNode $node): float|int
    {
        if ($node->operand === null) {
            throw new NullOperandException();
        }

        $inner = [];
        foreach ($node->operand as $operand) {
            $inner[] = $operand instanceof StringNode ? $operand->accept($this) : (float)$operand->accept($this);
        }

        if (!$inner) {
            throw new NullOperandException();
        }

        switch ($node->operator) {
            // Rounding functions
            case 'round':
                return round((float)$inner[0]);

            case 'floor':
                return floor((float)$inner[0]);

            case 'ceil':
                return ceil((float)$inner[0]);

            case 'ending':
                $ending = (string)$inner[0];
                $price  = (float)$inner[1];

                if (!preg_match('/\d*(\.\d\d)/', $ending)) {
                    throw new SyntaxErrorException();
                }

                $pure         = floor($price * 100) / 100;
                $pureString   = number_format($pure, 2, '.', '');
                $pureLenght   = strlen($pureString);
                $endingLenght = strlen($ending);
                $lenght       = $pureLenght - $endingLenght;

                if ($lenght <= 0) {
                    throw new SyntaxErrorException();
                }

                return (float)(substr($pureString, 0, $lenght) . $ending);

            default:
                throw new UnknownFunctionException($node->operator);
        }
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
