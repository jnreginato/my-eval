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
use MyEval\Extensions\Math;
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
use UnexpectedValueException;

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
class StdMathEvaluator implements Visitor
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
     * @throws SyntaxErrorException
     */
    public function visitBooleanNode(BooleanNode $node): bool
    {
        throw new SyntaxErrorException();
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
    public function visitVariableNode(VariableNode $node): float
    {
        $name = $node->value;

        if (array_key_exists($name, $this->variables)) {
            return (float)$this->variables[$name];
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
     * Evaluate a TernaryNode.
     *
     * @param TernaryExpressionNode $node AST to be evaluated.
     *
     * @return float
     * @throws SyntaxErrorException
     */
    public function visitTernaryNode(TernaryExpressionNode $node): float
    {
        throw new SyntaxErrorException();
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
            $inner[] = $operand?->accept($this);
        }

        if (!$inner) {
            throw new NullOperandException();
        }

        switch ($node->operator) {
            // Trigonometric functions
            case 'sin':
                return sin($inner[0]);

            case 'cos':
                return cos($inner[0]);

            case 'tan':
                return tan($inner[0]);

            case 'cot':
                $tan_inner = tan($inner[0]);
                if ($tan_inner === 0.0) {
                    return NAN;
                }
                return 1 / $tan_inner;

            // Trigonometric functions, argument in degrees
            case 'sind':
                return sin(deg2rad($inner[0]));

            case 'cosd':
                return cos(deg2rad($inner[0]));

            case 'tand':
                return tan(deg2rad($inner[0]));

            case 'cotd':
                $tan_inner = tan(deg2rad($inner[0]));
                if ($tan_inner === 0.0) {
                    return NAN;
                }
                return 1 / $tan_inner;

            // Inverse trigonometric functions
            case 'arcsin':
                return asin($inner[0]);

            case 'arccos':
                return acos($inner[0]);

            case 'arctan':
                return atan($inner[0]);

            case 'arccot':
                return M_PI / 2 - atan($inner[0]);

            // Exponential and logarithms
            case 'exp':
                return exp($inner[0]);

            case 'log':
            case 'ln':
                return log($inner[0]);

            case 'lg':
                return log10($inner[0]);

            // Powers
            case 'sqrt':
                return sqrt($inner[0]);

            // Hyperbolic functions
            case 'sinh':
                return sinh($inner[0]);

            case 'cosh':
                return cosh($inner[0]);

            case 'tanh':
                return tanh($inner[0]);

            case 'coth':
                $tanh_inner = tanh($inner[0]);
                if ($tanh_inner === 0.0) {
                    return NAN;
                }
                return 1 / $tanh_inner;

            // Inverse hyperbolic functions
            case 'arsinh':
                return asinh($inner[0]);

            case 'arcosh':
                return acosh($inner[0]);

            case 'artanh':
                return atanh($inner[0]);

            case 'arcoth':
                return atanh(1 / $inner[0]);

            case 'abs':
                return abs($inner[0]);

            case 'sgn':
                return $inner[0] >= 0 ? 1 : -1;

            case '!':
                $logGamma = Math::logGamma(1 + $inner[0]);

                return exp($logGamma);

            case '!!':
                if (round($inner[0]) !== $inner[0]) {
                    throw new UnexpectedValueException('Expecting positive integer (semi-factorial)');
                }
                return Math::semiFactorial((int)$inner[0]);

            // Rounding functions
            case 'round':
                return round($inner[0]);

            case 'floor':
                return floor($inner[0]);

            case 'ceil':
                return ceil($inner[0]);

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
}
