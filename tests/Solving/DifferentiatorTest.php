<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\RationalMathEval;
use PHPUnit\Framework\TestCase;

/**
 * Class DifferentiatorTest
 */
class DifferentiatorTest extends TestCase
{
    private RationalMathEval $parser;

    private Differentiator $differentiator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser         = new RationalMathEval();
        $this->differentiator = new Differentiator('x');

        parent::setUp();
    }

    /**
     * @param $node
     *
     * @return mixed
     */
    public function diff($node): mixed
    {
        return $node->accept($this->differentiator);
    }

    /**
     * @param $node1
     * @param $node2
     *
     * @return void
     */
    private function assertNodesEqual($node1, $node2): void
    {
        $printer = new TreePrinter();
        $message = 'Node 1: ' . $node1->accept($printer) . "\nNode 2: " . $node2->accept($printer) . "\n";

        static::assertTrue($node1->compareTo($node2), $message);
    }

    /**
     * @param $f
     * @param $df
     *
     * @return void
     */
    private function assertResult($f, $df): void
    {
        $fnc        = $this->parser->parse($f);
        $derivative = $this->parser->parse($df);

        $this->assertNodesEqual($this->diff($fnc), $derivative);

        // Check that Differentior leaves the original node unchanged.
        $newAST = $this->parser->parse($f);
        $this->assertNodesEqual($fnc, $newAST);
    }

    /**
     * @return void
     */
    public function testCanDifferentiateInteger(): void
    {
        $this->assertResult('1', '0');
        $this->assertResult('100', '0');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateNumber(): void
    {
        $this->assertResult('1.5', '0');
        $this->assertResult('0.3', '0');
    }

    /**
     * @return void
     */
    public function testCanRational(): void
    {
        $this->assertResult('1/5', '0');
        $this->assertResult('19/3', '0');
        $this->assertResult('x/3', '1/3');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateVariable(): void
    {
        $this->assertResult('x', '1');
        $this->assertResult('y', '0');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateConstant(): void
    {
        $this->assertResult('pi', '0');
        $this->assertResult('pi*e', '0');
        $this->assertResult('7', '0');
        $this->assertResult('1+3', '0');
        $this->assertResult('5*2', '0');
        $this->assertResult('1/2', '0');
        $this->assertResult('2^2', '0');
        $this->assertResult('-2', '0');
        $this->assertResult('NAN', 'NAN');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateUnaryMinus(): void
    {
        $this->assertResult('-x', '-1');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateSum(): void
    {
        $this->assertResult('x+sin(x)', '1+cos(x)');
        $this->assertResult('sin(x)+y', 'cos(x)');
        $this->assertResult('y+sin(x)', 'cos(x)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateDifference(): void
    {
        $this->assertResult('x-sin(x)', '1-cos(x)');
        $this->assertResult('sin(x)-y', 'cos(x)');
        $this->assertResult('sin(x)-sin(x)', '0');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateProduct(): void
    {
        $this->assertResult('x*sin(x)', 'x*cos(x)+sin(x)');
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCanDifferentiateQuotient(): void
    {
        $this->assertResult('x/sin(x)', '(sin(x)-x*cos(x))/sin(x)^2');
        $this->assertResult('x/1', '1');

        // The parser catches 'x/0', so create the test AST directly
        $f = new InfixExpressionNode('/', new VariableNode('x'), 0);
        $this->expectException(DivisionByZeroException::class);
        $this->diff($f);
    }

    /**
     * @return void
     */
    public function testCanDifferentiateExponent(): void
    {
        $this->assertResult('x^1', '1');
        $this->assertResult('x^2', '2x');
        $this->assertResult('x^3', '3x^2');
        $this->assertResult('x^x', 'x^x*(ln(x)+1)');
        $this->assertResult('x^(1/2)', '(1/2)*x^(-1/2)');
        $this->assertResult('e^x', 'e^x');
        $this->assertResult('e^(x^2)', '2*x*e^(x^2)');
        $this->assertResult('sin(x)^cos(x)', 'sin(x)^cos(x)*((-sin(x))*ln(sin(x))+cos(x)*cos(x)/sin(x))');
        $this->assertResult('x^2.0', '2.0x');
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     */
    public function testEmitExceptionIfExpressionNullOperand(): void
    {
        $differantiator = new Differentiator('x');
        $node           = new InfixExpressionNode('+', null, null);
        $this->expectException(NullOperandException::class);
        $differantiator->visitInfixExpressionNode($node);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     */
    public function testEmitExceptionIfExpressionUnknowOperator(): void
    {
        $differantiator = new Differentiator('x');
        $node           = new InfixExpressionNode('~', 1, 1);
        $this->expectException(UnknownOperatorException::class);
        $differantiator->visitInfixExpressionNode($node);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws UnknownOperatorException
     * @throws UnknownFunctionException
     * @throws UnexpectedOperatorException
     */
    public function testEmitExceptionIfFunctionNullOperand(): void
    {
        $differantiator = new Differentiator('x');
        $node           = new FunctionNode('sin');
        $this->expectException(NullOperandException::class);
        $differantiator->visitFunctionNode($node);
    }

    /**
     * @return void
     */
    public function testCanDifferentiateSin(): void
    {
        $this->assertResult('sin(x)', 'cos(x)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateCos(): void
    {
        $this->assertResult('cos(x)', '-sin(x)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateTan(): void
    {
        $this->assertResult('tan(x)', '1+tan(x)^2');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateCot(): void
    {
        $this->assertResult('cot(x)', '-1-cot(x)^2');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateArcsin(): void
    {
        $this->assertResult('arcsin(x)', '1/sqrt(1-x^2)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateArccos(): void
    {
        $this->assertResult('arccos(x)', '(-1)/sqrt(1-x^2)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateArctan(): void
    {
        $this->assertResult('arctan(x)', '1/(1+x^2)');
        $this->assertResult('arctan(x^3)', '(3x^2)/(1+(x^3)^2)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateArccot(): void
    {
        $this->assertResult('-arccot(x)', '1/(1+x^2)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateComposite(): void
    {
        $this->assertResult('sin(sin(x))', 'cos(x)*cos(sin(x))');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateExp(): void
    {
        $this->assertResult('exp(x)', 'exp(x)');
        $this->assertResult('exp(x^2)', '2*x*exp(x^2)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateLog(): void
    {
        $this->assertResult('log(x)', '1/x');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateLn(): void
    {
        $this->assertResult('ln(x)', '1/x');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateLog10(): void
    {
        $this->assertResult('log10(x)', '1/(ln(10)x)');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateSqrt(): void
    {
        $this->assertResult('sqrt(x)', '1/(2sqrt(x))');
    }

    /**
     * @return void
     */
    public function testCanDifferentiateHyperbolicFunctions(): void
    {
        $this->assertResult('sinh(x)', 'cosh(x)');
        $this->assertResult('cosh(x)', 'sinh(x)');
        $this->assertResult('tanh(x)', '1-tanh(x)^2');
        $this->assertResult('coth(x)', '1-coth(x)^2');

        $this->assertResult('arsinh(x)', '1/sqrt(x^2+1)');
        $this->assertResult('arcosh(x)', '1/sqrt(x^2-1)');
        $this->assertResult('artanh(x)', '1/(1-x^2)');
        $this->assertResult('arcoth(x)', '1/(1-x^2)');
    }

    /**
     * @return void
     */
    public function testCantDifferentiateAbs(): void
    {
        $this->assertResult('abs(x)', 'sgn(x)');
    }

    /**
     * @return void
     */
    public function testCannotDifferentiateUnknownFunction(): void
    {
        $node = new FunctionNode('erf', [new VariableNode('x')]);
        $this->expectException(UnknownFunctionException::class);

        $this->diff($node);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCannotDifferentiateUnknownOperator(): void
    {
        $this->expectException(UnknownOperatorException::class);
        $node = new InfixExpressionNode('%', new FloatNode(1), new VariableNode('x'));

        // We need to cheat here, since the ExpressionNode contructor already
        // throws an UnknownOperatorException when called with, say '%'
        // $node->setOperator('%');

        $this->diff($node);
    }

    /**
     * @return void
     */
    public function testCantDifferentiateCeil(): void
    {
        $f = $this->parser->parse('ceil(x)');

        $this->expectException(UnknownFunctionException::class);
        $this->diff($f);
    }

    /**
     * @return void
     */
    public function testCantDifferentiateFloor(): void
    {
        $f = $this->parser->parse('floor(x)');

        $this->expectException(UnknownFunctionException::class);
        $this->diff($f);
    }

    /**
     * @return void
     */
    public function testCantDifferentiateRound(): void
    {
        $f = $this->parser->parse('round(x)');

        $this->expectException(UnknownFunctionException::class);
        $this->diff($f);
    }

    /**
     * @return void
     */
    public function testCantDifferentiateSgn(): void
    {
        $f = $this->parser->parse('sgn(x)');

        $this->expectException(UnknownFunctionException::class);
        $this->diff($f);
    }
}
