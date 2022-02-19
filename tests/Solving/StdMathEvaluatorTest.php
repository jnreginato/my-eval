<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownVariableException;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\RationalMathEval;
use MyEval\StdMathEval;
use PHPUnit\Framework\TestCase;

/**
 * Class StdMathEvaluatorTest
 */
class StdMathEvaluatorTest extends TestCase
{
    private StdMathEval $parser;

    private RationalMathEval $rparser;

    private StdMathEvaluator $evaluator;

    private array $variables;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser  = new StdMathEval();
        $this->rparser = new RationalMathEval();

        $this->variables = ['x' => '0.7', 'y' => '2.1', 'i' => '5'];
        $this->evaluator = new StdMathEvaluator($this->variables);
    }

    /**
     * @param $f
     *
     * @return mixed
     */
    private function evaluate($f): mixed
    {
        return $f->accept($this->evaluator);
    }

    /**
     * @param $f
     *
     * @return mixed
     */
    private function compute($f): mixed
    {
        return $this->evaluate($this->parser->parse($f));
    }

    /**
     * @param $f
     * @param $x
     *
     * @return void
     */
    private function assertResult($f, $x): void
    {
        $value = $this->evaluate($this->parser->parse($f));
        static::assertEquals($value, $x);
    }

    /**
     * @param $f
     * @param $x
     *
     * @return void
     */
    private function assertApproximateResult($f, $x): void
    {
        $value = $this->evaluate($this->parser->parse($f));
        static::assertEqualsWithDelta($value, $x, 1e-7);
    }

    /**
     * @param $f
     *
     * @return void
     */
    private function assertNotANumber($f): void
    {
        $value = $this->evaluate($this->parser->parse($f));
        static::assertNan($value);
    }

    /**
     * @return void
     */
    public function testCanEvaluateNumber(): void
    {
        $this->assertResult('3', 3);
        $this->assertResult('-2', -2);
        $this->assertResult('3.0', 3.0);

        $node = $this->rparser->parse('1/2');
        static::assertEquals(0.5, $this->evaluate($node));
    }

    /**
     * @return void
     */
    public function testCanEvaluateConstant(): void
    {
        $this->assertResult('pi', M_PI);
        $this->assertResult('e', exp(1));

        $f = new ConstantNode('sdf');
        $this->expectException(UnknownConstantException::class);
        $this->evaluate($f);
    }

    public function testCanEvaluateVariable()
    {
        $this->assertResult('x', $this->variables['x']);
        $this->assertResult('i^2', $this->variables['i'] ** 2);

        $this->expectException(UnknownVariableException::class);

        $f = $this->parser->parse('q');
        $this->evaluate($f);
    }

    /**
     * @return void
     */
    public function testCanEvaluateAdditiion(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3+x', 3 + $x);
        $this->assertResult('3+x+1', 3 + $x + 1);
    }

    /**
     * @return void
     */
    public function testCanEvaluateSubtraction(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3-x', 3 - $x);
        $this->assertResult('3-x-1', 3 - $x - 1);
    }

    /**
     * @return void
     */
    public function testCanEvaluateUnaryMinus(): void
    {
        $this->assertResult('-x', -$this->variables['x']);
    }

    /**
     * @return void
     */
    public function testCanEvaluateMultiplication(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3*x', 3 * $x);
        $this->assertResult('3*x*2', 3 * $x * 2);
    }

    /**
     * @return void
     */
    public function testCanEvaluateDivision(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('3/x', 3 / $x);
        $this->assertResult('20/x/5', 20 / $x / 5);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCannotDivideByZero(): void
    {
        $f = new InfixExpressionNode('/', 3, 0);

        $this->expectException(DivisionByZeroException::class);
        $this->evaluate($f);
    }

    /**
     * @return void
     */
    public function testCanEvaluateExponentiation(): void
    {
        $x = $this->variables['x'];
        $this->assertResult('x^3', $x ** 3);
        $this->assertResult('x^x^x', $x ** ($x ** $x));
        $this->assertResult('(-1)^(-1)', -1);
    }

    /**
     * @return void
     */
    public function testCantRaise0To0(): void
    {
        $this->expectException(ExponentialException::class);
        $this->assertResult('0^0', 1);
    }

    /**
     * @return void
     */
    public function testExponentiationException1(): void
    {
        $f     = $this->parser->parse('0^(-1)');
        $value = $this->evaluate($f);
        static::assertTrue(is_infinite($value));
    }

    /**
     * @return void
     */
    public function testExponentiationException2(): void
    {
        $this->expectException(DivisionByZeroException::class);
        $f = $this->parser->parse('(-1)^(1/2)');
        $this->evaluate($f);
    }

    /**
     * @return void
     */
    public function testCanEvaluateSine(): void
    {
        $this->assertResult('sin(pi)', 0);
        $this->assertResult('sin(pi/2)', 1);
        $this->assertResult('sin(pi/6)', 0.5);
        $this->assertResult('sin(x)', sin((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateCosine(): void
    {
        $this->assertResult('cos(pi)', -1);
        $this->assertResult('cos(pi/2)', 0);
        $this->assertResult('cos(pi/3)', 0.5);
        $this->assertResult('cos(x)', cos((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateTangent(): void
    {
        $this->assertResult('tan(pi)', 0);
        $this->assertResult('tan(pi/4)', 1);
        $this->assertResult('tan(x)', tan((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateCotangent(): void
    {
        $this->assertResult('cot(pi/2)', 0);
        $this->assertResult('cot(pi/4)', 1);
        $this->assertResult('cot(x)', 1 / tan((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateArcsin(): void
    {
        $this->assertResult('arcsin(1)', M_PI / 2);
        $this->assertResult('arcsin(1/2)', M_PI / 6);
        $this->assertResult('arcsin(x)', asin((float)$this->variables['x']));

        $f     = $this->parser->parse('arcsin(2)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);
    }

    /**
     * @return void
     */
    public function testCanEvaluateArccos(): void
    {
        $this->assertResult('arccos(0)', M_PI / 2);
        $this->assertResult('arccos(1/2)', M_PI / 3);
        $this->assertResult('arccos(x)', acos((float)$this->variables['x']));

        $f     = $this->parser->parse('arccos(2)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);
    }

    /**
     * @return void
     */
    public function testCanEvaluateArctan(): void
    {
        $this->assertResult('arctan(1)', M_PI / 4);
        $this->assertResult('arctan(x)', atan((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateArccot()
    {
        $this->assertResult('arccot(1)', M_PI / 4);
        $this->assertResult('arccot(x)', M_PI / 2 - atan((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateExp(): void
    {
        $this->assertResult('exp(x)', exp((float)$this->variables['x']));
    }

    /**
     * @return void
     */
    public function testCanEvaluateLog(): void
    {
        $this->assertResult('log(x)', log((float)$this->variables['x']));

        $f     = $this->parser->parse('log(-1)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);
    }

    /**
     * @return void
     */
    public function testCanEvaluateLn(): void
    {
        $this->assertResult('ln(x)', log((float)$this->variables['x']));

        $f     = $this->parser->parse('ln(-1)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);
    }

    /**
     * @return void
     */
    public function testCanEvaluateLog10(): void
    {
        $this->assertResult('log10(x)', log((float)$this->variables['x']) / log(10));
    }

    /**
     * @return void
     */
    public function testCanEvaluateFactorial(): void
    {
        $this->assertResult('0!', 1);
        $this->assertResult('3!', 6);
        $this->assertResult('(3!)!', 720);
        $this->assertResult('5!/(2!3!)', 10);
        $this->assertResult('5!!', 15);
        $this->assertApproximateResult('4.12124!', 28.85455491);
    }

    /**
     * @return void
     */
    public function testCanEvaluateSqrt(): void
    {
        $this->assertResult('sqrt(x)', sqrt((float)$this->variables['x']));

        $f     = $this->parser->parse('sqrt(-2)');
        $value = $this->evaluate($f);

        $this->assertNaN($value);
    }

    /**
     * @return void
     */
    public function testCanEvaluateHyperbolicFunctions(): void
    {
        $x = 0.7;

        $this->assertResult('sinh(0)', 0);
        $this->assertResult('sinh(x)', sinh($x));

        $this->assertResult('cosh(0)', 1);
        $this->assertResult('cosh(x)', cosh($x));

        $this->assertResult('tanh(0)', 0);
        $this->assertResult('tanh(x)', tanh($x));

        $this->assertResult('coth(x)', 1 / tanh($x));

        $this->assertResult('arsinh(0)', 0);
        $this->assertResult('arsinh(x)', asinh($x));

        $this->assertResult('arcosh(1)', 0);
        $this->assertResult('arcosh(3)', acosh(3));

        $this->assertResult('artanh(0)', 0);
        $this->assertResult('artanh(x)', atanh($x));

        $this->assertResult('arcoth(3)', atanh(1 / 3));
    }

    /**
     * @return void
     */
    public function testCannotEvalauateUnknownFunction()
    {
        $f = new FunctionNode('sdf', [new FloatNode(1)]);

        $this->expectException(UnknownFunctionException::class);
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCannotEvaluateUnknownOperator(): void
    {
        $this->expectException(UnknownOperatorException::class);
        $node = new InfixExpressionNode('%', new FloatNode(1), new VariableNode('x'));
        // We need to cheat here, since the ExpressionNode contructor already
        // throws an UnknownOperatorException when called with, say '%'
        // $node->setOperator('%');

        $this->evaluate($node);
    }

    /**
     * @return void
     */
    public function testCanCreateTemporaryUnaryMinusNode(): void
    {
        $node = new InfixExpressionNode('~', null, null);
        static::assertEquals('~', $node->operator);
        static::assertNull($node->getRight());
        static::assertNull($node->getLeft());
        static::assertEquals(3, $node->precedence);
    }

    /**
     * @return InfixExpressionNode
     */
    public function testUnknownException(): InfixExpressionNode
    {
        $this->expectException(UnknownOperatorException::class);
        return new InfixExpressionNode('%', null, null);
    }

    /**
     * @return void
     */
    public function testEdgeCases(): void
    {
        $this->assertResult('0*log(0)', 0);

        $this->parser->setSimplifying(false);

        $this->assertNotANumber('0*log(0)');

        $this->expectException(ExponentialException::class);
        $this->assertResult('0^0', 1);
    }

    /**
     * @return void
     */
    public function testCanComputeExponentialsTwoWays(): void
    {
        static::assertEquals($this->compute('exp(1)'), $this->compute('e'));
        static::assertEquals($this->compute('exp(2)'), $this->compute('e^2'));
        static::assertEquals($this->compute('exp(-1)'), $this->compute('e^(-1)'));
        static::assertEquals($this->compute('exp(8)'), $this->compute('e^8'));
        static::assertEquals($this->compute('exp(22)'), $this->compute('e^22'));
    }

    /**
     * @return void
     */
    public function testCanComputeSpecialValues(): void
    {
        $this->assertNotANumber('cot(0)');
        $this->assertNotANumber('cotd(0)');
        $this->assertNotANumber('coth(0)');
    }

    /**
     * @return void
     */
    public function testCanComputeRoundingFunctions(): void
    {
        $this->assertResult('ceil(1+2.3)', 4);
        $this->assertResult('floor(2*2.3)', 4);
        $this->assertResult('ceil(2*2.3)', 5);
        $this->assertResult('round(2*2.3)', 5);
    }
}
