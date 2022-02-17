<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Operations\OperationBuilder;

/**
 * Differentiate an abstract syntax tree (AST).
 *
 * Implementation of a Visitor, transforming an AST into another AST representing the derivative of the original AST.
 *
 * The class implements differentiation rules for all arithmetic operators as well as every elementary function
 * recognized by StdMathLexer and Parser, handling for example the product rule and the chain rule correctly.
 *
 * To keep the resulting AST reasonably simple, a number of simplification rules are built in.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\StdMathEval;
 * use MyEval\Solving\Differentiator;
 *
 * $parser = new StdMathEval();
 * $ast = $parser->parse('exp(2x)+xy');
 * $ddx = new Differentiator('x');     // Create a d/dx operator
 * $df = $ast->accept($ddx);           // $df now contains the AST of '2exp(2x)+y'
 *
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\Differentiator;
 *
 * // Tokenize
 * $lexer = new StdMathLexer();
 * $tokens = $lexer->tokenize('exp(2x)+xy');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Differentiate
 * $differentiator = new Differentiator('x');  // Create a d/dx operator
 * $value = $ast->accept($differentiator);     // $df now contains the AST of '2exp(2x)+y'
 * ~~~
 */
class Differentiator implements Visitor
{
    /**
     * Create a Differentiator.
     *
     * @param string           $variable    Variable that we differentiate with respect to.
     * @param OperationBuilder $nodeFactory Used for building the resulting AST.
     */
    public function __construct(
        private string $variable,
        private OperationBuilder $nodeFactory = new OperationBuilder()
    ) {
    }

    /**
     * Differentiate an IntegerNode.
     *
     * Create a NumberNode representing '0'. (The derivative of a constant is indentically 0).
     *
     * @param IntegerNode $node AST to be differentiated.
     *
     * @return IntegerNode
     */
    public function visitIntegerNode(IntegerNode $node): IntegerNode
    {
        return new IntegerNode(0);
    }

    /**
     * Differentiate a RationalNode.
     *
     * Create a NumberNode representing '0'. (The derivative of a constant is indentically 0).
     *
     * @param RationalNode $node AST to be differentiated.
     *
     * @return IntegerNode
     **/
    public function visitRationalNode(RationalNode $node): IntegerNode
    {
        return new IntegerNode(0);
    }

    /**
     * Differentiate a NumberNode.
     *
     * Create a NumberNode representing '0'. (The derivative of a constant is indentically 0).
     *
     * @param FloatNode $node AST to be differentiated.
     *
     * @return IntegerNode
     */
    public function visitNumberNode(FloatNode $node): IntegerNode
    {
        return new IntegerNode(0);
    }

    /**
     * Differentiate a BooleanNode.
     *
     * @return void
     * @throws SyntaxErrorException
     */
    public function visitBooleanNode(): void
    {
        throw new SyntaxErrorException();
    }

    /**
     * Differentiate a VariableNode.
     *
     * Create a NumberNode representing '0' or '1' depending on the differetiation variable.
     *
     * @param VariableNode $node AST to be differentiated.
     *
     * @return IntegerNode
     */
    public function visitVariableNode(VariableNode $node): IntegerNode
    {
        if ($node->value === $this->variable) {
            return new IntegerNode(1);
        }

        return new IntegerNode(0);
    }

    /**
     * Differentiate a ConstantNode.
     *
     * Create a NumberNode representing '0'. (The derivative of a constant is identically 0).
     *
     * @param ConstantNode $node AST to be differentiated.
     *
     * @return IntegerNode|ConstantNode
     */
    public function visitConstantNode(ConstantNode $node): IntegerNode|ConstantNode
    {
        if ($node->value === 'NAN') {
            return $node;
        }

        return new IntegerNode(0);
    }

    /**
     * Differentiate an InfixExpressionNode.
     *
     * Using the usual rules for differentiating, create an ExpressionNode giving an AST correctly representing the
     * derivative `(x op y)'` where `op` is one of `+`, `-`, `*`, `/` or `^`.
     *
     * ## Differentiation rules:
     *
     * - \\( (f+g)' = f' + g' \\)
     * - \\( (f-g) ' = f' - g' \\)
     * - \\( (-f)' = -f' \\)
     * - \\( (f*g)' = f'g + f g' \\)
     * - \\( (f/g)' = (f' g - f g')/g^2 \\)
     * - \\( (f^g)' = f^g  (g' \\log(f) + gf'/f) \\) with a simpler expression when g is a NumberNode.
     *
     * @param InfixExpressionNode $node AST to be differentiated.
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException If the operator is something other than `+`, `-`, `*`, `/` or `^`.
     * @throws ExponentialException
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node): Node
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
                return $this->nodeFactory->addition($left->accept($this), $right->accept($this));

            case '-':
                return $this->nodeFactory->subtraction($left->accept($this), $right?->accept($this));

            // Product rule (fg)' = fg' + f'g
            case '*':
                return $this->nodeFactory->addition(
                    $this->nodeFactory->multiplication($node->getLeft(), $right->accept($this)),
                    $this->nodeFactory->multiplication($left->accept($this), $node->getRight())
                );

            // Quotient rule (f/g)' = (f'g - fg')/g^2
            case '/':
                $term1       = $this->nodeFactory->multiplication($left->accept($this), $node->getRight());
                $term2       = $this->nodeFactory->multiplication($node->getLeft(), $right->accept($this));
                $numerator   = $this->nodeFactory->subtraction($term1, $term2);
                $denominator = $this->nodeFactory->exponentiation($node->getRight(), new IntegerNode(2));

                return $this->nodeFactory->division($numerator, $denominator);

            // f^g = exp(g log(f)), so (f^g)' = f^g (g'log(f) + g/f)
            case '^':
                $base     = $left;
                $exponent = $right;

                if ($exponent instanceof IntegerNode) {
                    $fpow = $this->nodeFactory->exponentiation($base, new IntegerNode($exponent->value - 1));

                    return $this->nodeFactory->multiplication(
                        new IntegerNode($exponent->value),
                        $this->nodeFactory->multiplication($fpow, $base->accept($this))
                    );
                }

                if ($exponent instanceof FloatNode) {
                    $fpow = $this->nodeFactory->exponentiation($base, new FloatNode($exponent->value - 1));

                    return $this->nodeFactory->multiplication(
                        new FloatNode($exponent->value),
                        $this->nodeFactory->multiplication($fpow, $base->accept($this))
                    );
                }

                if ($exponent instanceof RationalNode) {
                    $power = new RationalNode(
                        $exponent->getNumerator() - $exponent->getDenominator(),
                        $exponent->getDenominator()
                    );
                    $fpow  = $this->nodeFactory->exponentiation($base, $power);

                    return $this->nodeFactory->multiplication(
                        $exponent,
                        $this->nodeFactory->multiplication($fpow, $base->accept($this))
                    );
                }

                if ($base instanceof ConstantNode && $base->value === 'e') {
                    return $this->nodeFactory->multiplication($right->accept($this), $node);
                }

                $term1   = $this->nodeFactory->multiplication($right->accept($this), new FunctionNode('ln', $base));
                $term2   = $this->nodeFactory->division(
                    $this->nodeFactory->multiplication($exponent, $base->accept($this)),
                    $base
                );
                $factor2 = $this->nodeFactory->addition($term1, $term2);

                return $this->nodeFactory->multiplication($node, $factor2);

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
     * Differentiate a FunctionNode.
     *
     * Create an ExpressionNode giving an AST representing the derivative `f'` where `f` is an elementary function.
     *
     * ## Differentiation rules:
     *
     *  \\( \\sin(f(x))' = f'(x)  \\cos(f(x)) \\)
     *  \\( \\cos(f(x))' = -f'(x)  \\sin(f(x)) \\)
     *  \\( \\tan(f(x))' = f'(x) (1 + \\tan(f(x))^2 \\)
     *  \\( \\operatorname{cot}(f(x))' = f'(x) (-1 - \\operatorname{cot}(f(x))^2 \\)
     *  \\( \\arcsin(f(x))' = f'(x) / \\sqrt{1-f(x)^2} \\)
     *  \\( \\arccos(f(x))' = -f'(x) / \\sqrt{1-f(x)^2} \\)
     *  \\( \\arctan(f(x))' = f'(x) / (1+f(x)^2) \\)
     *  \\( \\operatorname{arccot}(f(x))' = -f'(x) / (1+f(x)^2) \\)
     *  \\( \\exp(f(x))' = f'(x) \\exp(f(x)) \\)
     *  \\( \\log(f(x))' = f'(x) / f(x) \\)
     *  \\( \\ln(f(x))' = f'(x) / (\\log(10) * f(x)) \\)
     *  \\( \\sqrt{f(x)}' = f'(x) / (2 \\sqrt{f(x)} \\)
     *  \\( \\sinh(f(x))' = f'(x) \\cosh(f(x)) \\)
     *  \\( \\cosh(f(x))' = f'(x) \\sinh(f(x)) \\)
     *  \\( \\tanh(f(x))' = f'(x) (1-\\tanh(f(x))^2) \\)
     *  \\( \\operatorname{coth}(f(x))' = f'(x) (1-\\operatorname{coth}(f(x))^2) \\)
     *  \\( \\operatorname{arsinh}(f(x))' = f'(x) / \\sqrt{f(x)^2+1} \\)
     *  \\( \\operatorname{arcosh}(f(x))' = f'(x) / \\sqrt{f(x)^2-1} \\)
     *  \\( \\operatorname{artanh}(f(x))' = f'(x) (1-f(x)^2) \\)
     *  \\( \\operatorname{arcoth}(f(x))' = f'(x) (1-f(x)^2) \\)
     *
     * @param FunctionNode $node AST to be differentiated.
     *
     * @return Node
     * @throws DivisionByZeroException If results a division by zero.
     * @throws NullOperandException If operand is null.
     * @throws UnknownFunctionException If the function name doesn't match one of the above.
     * @throws UnknownOperatorException If the operator name is unknow.
     * @throws UnexpectedOperatorException
     * @throws ExponentialException
     */
    public function visitFunctionNode(FunctionNode $node): Node
    {
        if ($node->operand === null) {
            throw new NullOperandException();
        }

        $inner = $node->operand->accept($this);
        $arg   = $node->operand;

        switch ($node->operator) {
            case 'sin':
                $df = new FunctionNode('cos', $arg);
                break;

            case 'cos':
                $sin = new FunctionNode('sin', $arg);
                $df  = $this->nodeFactory->unaryMinus($sin);
                break;

            case 'tan':
                $tansquare = $this->nodeFactory->exponentiation($node, new IntegerNode(2));
                $df        = $this->nodeFactory->addition(new IntegerNode(1), $tansquare);
                break;

            case 'cot':
                $unaryMinus = $this->nodeFactory->unaryMinus(new IntegerNode(1));
                $cotsquare  = $this->nodeFactory->exponentiation($node, new IntegerNode(2));
                $df         = $this->nodeFactory->subtraction($unaryMinus, $cotsquare);
                break;

            case 'arcsin':
                $exp   = $this->nodeFactory->exponentiation($arg, new IntegerNode(2));
                $denom = new FunctionNode('sqrt', $this->nodeFactory->subtraction(new IntegerNode(1), $exp));
                return $this->nodeFactory->division($inner, $denom);

            case 'arccos':
                $exp   = $this->nodeFactory->exponentiation($arg, new IntegerNode(2));
                $denom = new FunctionNode('sqrt', $this->nodeFactory->subtraction(new IntegerNode(1), $exp));
                return $this->nodeFactory->division($this->nodeFactory->unaryMinus($inner), $denom);

            case 'arctan':
                $denom = $this->nodeFactory->addition(
                    new IntegerNode(1),
                    $this->nodeFactory->exponentiation($arg, new IntegerNode(2))
                );
                return $this->nodeFactory->division($inner, $denom);

            case 'arccot':
                $denom = $this->nodeFactory->addition(
                    new IntegerNode(1),
                    $this->nodeFactory->exponentiation($arg, new IntegerNode(2))
                );
                $df    = $this->nodeFactory->unaryMinus($this->nodeFactory->division(new IntegerNode(1), $denom));
                break;

            case 'exp':
                $df = new FunctionNode('exp', $arg);
                break;

            case 'ln':
            case 'log':
                return $this->nodeFactory->division($inner, $arg);

            case 'lg':
                $denominator = $this->nodeFactory->multiplication(new FunctionNode('ln', new IntegerNode(10)), $arg);
                return $this->nodeFactory->division($inner, $denominator);

            case 'sqrt':
                $denom = $this->nodeFactory->multiplication(new IntegerNode(2), $node);
                return $this->nodeFactory->division($inner, $denom);

            case 'sinh':
                $df = new FunctionNode('cosh', $arg);
                break;

            case 'cosh':
                $df = new FunctionNode('sinh', $arg);
                break;

            case 'tanh':
                $tanhsquare = $this->nodeFactory->exponentiation(new FunctionNode('tanh', $arg), new IntegerNode(2));
                $df         = $this->nodeFactory->subtraction(new IntegerNode(1), $tanhsquare);
                break;

            case 'coth':
                $cothsquare = $this->nodeFactory->exponentiation(new FunctionNode('coth', $arg), new IntegerNode(2));
                $df         = $this->nodeFactory->subtraction(new IntegerNode(1), $cothsquare);
                break;

            case 'arsinh':
                $exp  = $this->nodeFactory->exponentiation($arg, new IntegerNode(2));
                $temp = $this->nodeFactory->addition($exp, new IntegerNode(1));
                return $this->nodeFactory->division($inner, new FunctionNode('sqrt', $temp));

            case 'arcosh':
                $exp  = $this->nodeFactory->exponentiation($arg, new IntegerNode(2));
                $temp = $this->nodeFactory->subtraction($exp, new IntegerNode(1));
                return $this->nodeFactory->division($inner, new FunctionNode('sqrt', $temp));

            case 'artanh':
            case 'arcoth':
                $exp         = $this->nodeFactory->exponentiation($arg, new IntegerNode(2));
                $denominator = $this->nodeFactory->subtraction(new IntegerNode(1), $exp);
                return $this->nodeFactory->division($inner, $denominator);

            case 'abs':
                $df = new FunctionNode('sgn', $arg);
                break;

            default:
                throw new UnknownFunctionException($node->operator);
        }

        return $this->nodeFactory->multiplication($inner, $df);
    }
}
