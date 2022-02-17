<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\ComplexMathEval;
use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\LogarithmOfZeroException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownVariableException;
use MyEval\Extensions\Complex;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Class ComplexEvaluatorTest
 */
class ComplexEvaluatorTest extends TestCase
{
    private ComplexMathEval $parser;

    private ComplexEvaluator $evaluator;

    private array $variables;

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    protected function setUp(): void
    {
        $this->parser = new ComplexMathEval();

        $this->variables = ['x' => Complex::parse('1+i'), 'y' => Complex::parse('3+2i')];
        $this->evaluator = new ComplexEvaluator($this->variables);

        parent::setUp();
    }

    /**
     * @param Node $ast
     *
     * @return mixed
     */
    private function evaluate(Node $ast): mixed
    {
        return $ast->accept($this->evaluator);
    }

    /**
     * @param $f
     * @param $x
     *
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    private function assertResult($f, $x): void
    {
        $value = $this->evaluate($this->parser->parse($f));
        if (!($x instanceof Complex)) {
            $x = Complex::parse($x);
        }

        static::assertEquals($value->real, $x->real);
        static::assertEquals($value->imaginary, $x->imaginary);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateNumber(): void
    {
        $this->assertResult('3', new Complex(3, 0));
        $this->assertResult('-2', new Complex(-2, 0));
        $this->assertResult('1+i', new Complex(1, 1));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateVariable(): void
    {
        $this->assertResult('x', $this->variables['x']);

        $this->expectException(UnknownVariableException::class);

        $f = $this->parser->parse('q');
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateConstant(): void
    {
        $this->assertResult('pi', M_PI);
        $this->assertResult('i', new Complex(0, 1));

        $f = new ConstantNode('sdf');
        $this->expectException(UnknownConstantException::class);
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateAdditiion(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3+x', Complex::add(3, $x));
        $this->assertResult('3+x+1', Complex::add(4, $x));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateSubtraction(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3-x', Complex::sub(3, $x));
        $this->assertResult('3-x-1', Complex::sub(2, $x));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateUnaryMinus(): void
    {
        $this->assertResult('-x', Complex::mul(-1, $this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateMultiplication(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3*x', Complex::mul(3, $x));
        $this->assertResult('3*x*2', Complex::mul(6, $x));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateDivision(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3/x', Complex::div(3, $x));
        $this->assertResult('20/x/5', Complex::div(4, $x));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateExponentiation(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('x^3', Complex::pow($x, 3));
        $this->assertResult('x^x^x', Complex::pow($x, Complex::pow($x, $x)));
        $this->assertResult('(-1)^(-1)', Complex::parse(-1));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCantRaise0To0(): void
    {
        $this->expectException(ExponentialException::class);
        $this->assertResult('0^0', 1);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnknownOperatorException
     */
    public function testEmitExceptionIfExpressionNullOperand(): void
    {
        $complexEvaluator = new ComplexEvaluator();
        $node             = new InfixExpressionNode('+', null, null);
        $this->expectException(NullOperandException::class);
        $complexEvaluator->visitInfixExpressionNode($node);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnknownOperatorException
     */
    public function testEmitExceptionIfExpressionUnknowOperator(): void
    {
        $complexEvaluator = new ComplexEvaluator();
        $node             = new InfixExpressionNode('~', 1, 1);
        $this->expectException(UnknownOperatorException::class);
        $complexEvaluator->visitInfixExpressionNode($node);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnknownFunctionException
     */
    public function testEmitExceptionIfFunctionNullOperand(): void
    {
        $complexEvaluator = new ComplexEvaluator();
        $node             = new FunctionNode('sin', null);
        $this->expectException(NullOperandException::class);
        $complexEvaluator->visitFunctionNode($node);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateSine(): void
    {
        $this->assertResult('sin(0)', 0);
        $this->assertResult('sin(pi/2)', 1);
        $this->assertResult('sin(pi/6)', 0.5);
        $this->assertResult('sin(x)', Complex::sin($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateCosine(): void
    {
        $this->assertResult('cos(pi)', -1);
        $this->assertResult('cos(pi/2)', 0);
        $this->assertResult('cos(pi/3)', 0.5);
        $this->assertResult('cos(x)', Complex::cos($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateTangent(): void
    {
        $this->assertResult('tan(pi)', 0);
        $this->assertResult('tan(pi/4)', 1);
        $this->assertResult('tan(x)', Complex::tan($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateCotangent(): void
    {
        $this->assertResult('cot(pi/2)', 0);
        $this->assertResult('cot(pi/4)', 1);
        $this->assertResult('cot(x)', Complex::div(1, Complex::tan($this->variables['x'])));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateArcsin(): void
    {
        $this->assertResult('arcsin(1)', M_PI / 2);
        $this->assertResult('arcsin(1/2)', M_PI / 6);
        $this->assertResult('arcsin(x)', Complex::arcsin($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateArccos(): void
    {
        $this->assertResult('arccos(0)', M_PI / 2);
        $this->assertResult('arccos(1/2)', M_PI / 3);
        $this->assertResult('arccos(x)', Complex::arccos($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateArctan(): void
    {
        $this->assertResult('arctan(1)', M_PI / 4);
        $this->assertResult('arctan(x)', Complex::arctan($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateArccot(): void
    {
        $this->assertResult('arccot(1)', M_PI / 4);
        $this->assertResult('arccot(x)', Complex::arccot($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateExp(): void
    {
        $this->assertResult('exp(x)', Complex::exp($this->variables['x']));
        $this->assertResult('e^x', Complex::exp($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateLog(): void
    {
        $this->assertResult('log(-1)', new Complex(0, M_PI));
        $this->assertResult('log(x)', Complex::log($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateLn(): void
    {
        $this->assertResult('ln(3)', new Complex(log(3), 0.0));

        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('ln(x)', Complex::log($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateLog10(): void
    {
        $this->assertResult('lg(-1)', new Complex(0, M_PI / log(10)));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateSqrt(): void
    {
        $this->assertResult('sqrt(-1)', new Complex(0, 1));
        $this->assertResult('sqrt(x)', Complex::sqrt($this->variables['x']));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateAbs(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('abs(x)', $x->abs());
        $this->assertResult('abs(i)', 1);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateArg(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('arg(x)', $x->arg());
        $this->assertResult('arg(1+i)', M_PI / 4);
        $this->assertResult('arg(-i)', -M_PI / 2);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateConj(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('conj(x)', new Complex($x->real, -$x->imaginary));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateRe(): void
    {
        $y = $this->variables['y'];
        $this->assertResult('re(y)', $y->real);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanEvaluateIm(): void
    {
        $y = $this->variables['y'];
        $this->assertResult('im(y)', $y->imaginary);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanEvaluateHyperbolicFunctions(): void
    {
        $x = $this->variables['x'];

        $this->assertResult('sinh(0)', 0);
        $this->assertResult('sinh(x)', Complex::sinh($x));

        $this->assertResult('cosh(0)', 1);
        $this->assertResult('cosh(x)', Complex::cosh($x));

        $this->assertResult('tanh(0)', 0);
        $this->assertResult('tanh(x)', Complex::tanh($x));

        $this->assertResult('coth(x)', Complex::div(1, Complex::tanh($x)));

        $this->assertResult('arsinh(0)', 0);
        $this->assertResult('arsinh(x)', Complex::arsinh($x));

        $this->assertResult('arcosh(1)', 0);
        $this->assertResult('arcosh(3)', Complex::arcosh(3));
        $this->assertResult('arcosh(x)', Complex::arcosh($x));

        $this->assertResult('artanh(0)', 0);
        $this->assertResult('artanh(x)', Complex::artanh($x));

        $this->assertResult('arcoth(x)', Complex::div(1, Complex::artanh($x)));
    }

    /**
     * @return void
     */
    public function testCannotEvalauateUnknownFunction(): void
    {
        $f = new FunctionNode('sdf', new FloatNode(1));

        $this->expectException(UnknownFunctionException::class);
        $this->evaluate($f);
    }

    /**
     * @return Node
     */
    public function testUnknownException(): Node
    {
        $this->expectException(UnknownOperatorException::class);
        return new InfixExpressionNode('%', null, null);
    }

    /**
     * @return void
     */
    public function testEdgeCases(): void
    {
        $this->expectException(LogarithmOfZeroException::class);
        $this->evaluate($this->parser->parse('log(0)'));

        $this->expectException(LogarithmOfZeroException::class);
        $this->evaluate($this->parser->parse('arctan(i)'));
    }
}
