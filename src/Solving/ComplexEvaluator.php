<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\LogarithmOfZeroException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownVariableException;
use MyEval\Extensions\Complex;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use UnexpectedValueException;

use function array_key_exists;

/**
 * Evaluate a parsed mathematical expression with complex numbers.
 *
 * Implementation of a Visitor, transforming an AST into a complex number, giving the *value* of the expression
 * represented by the AST.
 *
 * The class implements evaluation of all arithmetic operators as well as every elementary function and predefined
 * constant recognized by Lexer and ComplexMathLexer.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\ComplexMathEval;
 *
 * $evaluator = new ComplexMathEval();
 * $result = $evaluator->evaluate('x+y', [ 'x' => '2+4i', 'y' => -1 ]);  // Results 1+4i.
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\ComplexMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\ComplexEvaluator;
 *
 * // Tokenize
 * $lexer = new ComplexMathLexer();
 * $tokens = $lexer->tokenize('x + y');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Evaluate
 * $evaluator = new ComplexEvaluator([ 'x' => '3' ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class ComplexEvaluator implements Visitor
{
    /**
     * @var array $variables Key/value pair holding current values of the variables used for evaluating.
     */
    private array $variables;

    /**
     * Create a ComplexEvaluator with given variable values.
     *
     * @param mixed|null $variables Key-value array of variables with corresponding values.
     *
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public function __construct(array $variables = [])
    {
        $this->variables = [];
        foreach ($variables as $var => $value) {
            $this->variables[$var] = Complex::parse($value);
        }
    }

    /**
     * Evaluate an IntegerNode.
     *
     * @param IntegerNode $node AST to be evaluated.
     *
     * @return Complex
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function visitIntegerNode(IntegerNode $node): Complex
    {
        return Complex::create($node->value, 0);
    }

    /**
     * Evaluate a RationalNode.
     *
     * @param RationalNode $node AST to be evaluated.
     *
     * @return Complex
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function visitRationalNode(RationalNode $node): Complex
    {
        return Complex::create((string)$node, 0);
    }

    /**
     * Evaluate a NumberNode.
     *
     * @param FloatNode $node AST to be evaluated.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public function visitNumberNode(FloatNode $node): Complex
    {
        return Complex::create($node->value, 0);
    }

    /**
     * Evaluate a BooleanNode.
     *
     * @return void
     * @throws SyntaxErrorException
     */
    public function visitBooleanNode(): void
    {
        throw new SyntaxErrorException();
    }

    /**
     * Evaluate a VariableNode.
     *
     * Returns the current value of a VariableNode, as defined by the constructor.
     *
     * @param VariableNode $node AST to be evaluated.
     *
     * @return Complex
     * @throws UnknownVariableException If the variable respresented by the VariableNode is *not* set.
     */
    public function visitVariableNode(VariableNode $node): Complex
    {
        $name = $node->value;

        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }

        throw new UnknownVariableException($name);
    }

    /**
     * Evaluate a ConstantNode.
     *
     * Returns the value of a ConstantNode recognized by ComplexMathLexer.
     *
     * @param ConstantNode $node AST to be evaluated
     *
     * @return Complex
     * @throws UnknownConstantException if the variable represented by the ConstantNode is *not* recognized.
     */
    public function visitConstantNode(ConstantNode $node): Complex
    {
        return match ($node->value) {
            'pi'    => new Complex(M_PI, 0),
            'e'     => new Complex(M_E, 0),
            'i'     => new Complex(0, 1),
            default => throw new UnknownConstantException($node->value),
        };
    }

    /**
     * Evaluate an InfixExpressionNode.
     *
     * Computes the value of an InfixExpressionNode `x op y` where `op` is one of `+`, `-`, `*`, `/` or `^`.
     *
     * @param InfixExpressionNode $node AST to be evaluated.
     *
     * @return Complex
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     * @throws NullOperandException
     * @throws UnknownOperatorException If the operator is something other than `+`, `-`, `*`, `/` or `^`.
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node): Complex
    {
        $left     = $node->getLeft();
        $operator = $node->operator;
        $right    = $node->getRight();

        if ($left === null || ($right === null && $operator !== '-')) {
            throw new NullOperandException();
        }

        // Perform the right operation based on the operator.
        switch ($operator) {
            case '+':
                return Complex::add($left->accept($this), $right->accept($this));
            case '-':
                if ($right === null) {
                    return Complex::mul($left->accept($this), -1);
                }
                return Complex::sub($left->accept($this), $right->accept($this));
            case '*':
                return Complex::mul($left->accept($this), $right->accept($this));
            case '/':
                return Complex::div($left->accept($this), $right->accept($this));
            case '^':
                // This needs to be improved.
                return Complex::pow($left->accept($this), $right->accept($this));
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
     * Evaluate a FunctionNode.
     *
     * Computes the value of a FunctionNode `f(x)`, where f is an elementary function recognized by ComplexMathLexer.
     *
     * @param FunctionNode $node AST to be evaluated
     *
     * @return Complex
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnknownFunctionException If the function represented by the FunctionNode is *not* recognized.
     */
    public function visitFunctionNode(FunctionNode $node): Complex
    {
        if ($node->operand === null) {
            throw new NullOperandException();
        }

        $z = $node->operand->accept($this);

        switch ($node->operator) {
            // Trigonometric functions
            case 'sin':
                return Complex::sin($z);

            case 'cos':
                return Complex::cos($z);

            case 'tan':
                return Complex::tan($z);

            case 'cot':
                return Complex::cot($z);

            // Inverse trigonometric functions
            case 'arcsin':
                return Complex::arcsin($z);

            case 'arccos':
                return Complex::arccos($z);

            case 'arctan':
                return Complex::arctan($z);

            case 'arccot':
                return Complex::arccot($z);

            case 'sinh':
                return Complex::sinh($z);

            case 'cosh':
                return Complex::cosh($z);

            case 'tanh':
                return Complex::tanh($z);

            case 'coth':
                return Complex::div(1, Complex::tanh($z));

            case 'arsinh':
                return Complex::arsinh($z);

            case 'arcosh':
                return Complex::arcosh($z);

            case 'artanh':
                return Complex::artanh($z);

            case 'arcoth':
                return Complex::div(1, Complex::artanh($z));

            case 'exp':
                return Complex::exp($z);

            case 'ln':
                if ($z->imaginary !== 0.0 || $z->real <= 0) {
                    throw new UnexpectedValueException('Expecting positive real number (ln)');
                }
                return Complex::log($z);

            case 'log':
                return Complex::log($z);

            case 'lg':
                return Complex::div(Complex::log($z), M_LN10);

            case 'sqrt':
                return Complex::sqrt($z);

            case 'abs':
                return new Complex($z->abs(), 0);

            case 'arg':
                return new Complex($z->arg(), 0);

            case 're':
                return new Complex($z->real, 0);

            case 'im':
                return new Complex($z->imaginary, 0);

            case 'conj':
                return new Complex($z->real, -$z->imaginary);

            default:
                throw new UnknownFunctionException($node->operator);
        }
    }
}
