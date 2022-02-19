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
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\RationalMathEval;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Class RationalEvaluatorTest
 */
class RationalEvaluatorTest extends TestCase
{
    private RationalMathEval $parser;

    private RationalEvaluator $evaluator;

    private array $variables;

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    protected function setUp(): void
    {
        $this->parser = new RationalMathEval();

        $this->variables = ['x' => '1/2', 'y' => '2/3'];
        $this->evaluator = new RationalEvaluator($this->variables);

        parent::setUp();
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
     * @param $x
     *
     * @return void
     */
    private function assertResult($f, $x)
    {
        $value = $this->evaluate($this->parser->parse($f));
        static::assertEquals($value, $x);
    }

    /**
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateNumber(): void
    {
        $this->assertResult('3', new RationalNode(3, 1));
        $this->assertResult('-2', new RationalNode(-2, 1));
        $this->assertResult('1/2', new RationalNode(1, 2));
    }

    /**
     * @return void
     */
    public function testCantEvaluateFloat(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('2.5', 2.5);
    }

    /**
     * @return void
     */
    public function testCanEvaluateConstant(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('pi', M_PI);
    }

    /**
     * @return void
     */
    public function testUnknownConstant(): void
    {
        $f = new ConstantNode('sdf');
        $this->expectException(UnknownConstantException::class);
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateVariable(): void
    {
        $this->assertResult('x', $this->evaluator->parseRational($this->variables['x']));

        $this->expectException(UnknownVariableException::class);

        $f = $this->parser->parse('q');
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateAdditiion(): void
    {
        $this->assertResult('3+x', new RationalNode(7, 2));
        $this->assertResult('3+x+1', new RationalNode(9, 2));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateSubtraction(): void
    {
        $this->assertResult('3-x', new RationalNode(5, 2));
        $this->assertResult('3-x-1', new RationalNode(3, 2));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateUnaryMinus(): void
    {
        $this->assertResult('-x', new RationalNode(-1, 2));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateMultiplication(): void
    {
        $this->assertResult('3*x', new RationalNode(3, 2));
        $this->assertResult('3*x*2', new RationalNode(3, 1));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateDivision(): void
    {
        $this->assertResult('3/x', new RationalNode(6, 1));
        $this->assertResult('20/x/5', new RationalNode(8, 1));
    }

    /**
     * @return void
     * @throws UnknownOperatorException
     */
    public function testCannotDivideByZero(): void
    {
        $f = new InfixExpressionNode('/', new IntegerNode(3), new IntegerNode(0));

        $this->expectException(DivisionByZeroException::class);
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateExponentiation(): void
    {
        $this->assertResult('x^3', new RationalNode(1, 8));
        $this->assertResult('x^(-3)', new RationalNode(8, 1));
        $this->assertResult('(-x)^3', new RationalNode(-1, 8));
        $this->assertResult('(-x)^(-3)', new RationalNode(-8, 1));
        $this->assertResult('(-1)^(-1)', new RationalNode(-1, 1));
        $this->assertResult('4^(-3/2)', new RationalNode(1, 8));
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
    public function testExponentiationExceptions(): void
    {
        $this->expectException(DivisionByZeroException::class);
        $f = $this->parser->parse('0^(-1)');
        $this->evaluate($f);
    }

    /**
     * @return void
     */
    public function testCanEvaluateSine(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('sin(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateCosine(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('cos(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateTangent(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('tan(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateCotangent(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('cot(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateArcsin(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('arcsin(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateArccos(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('arccos(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateArctan(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('arctan(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateArccot(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('arccot(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateExp(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('exp(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateLog(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('log(x)', 0);
    }

    /**
     * @return void
     */
    public function testCanEvaluateLog10(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('log10(x)', 0);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanEvaluateSqrt(): void
    {
        $this->assertResult('sqrt(1/4)', new RationalNode(1, 2));
        $this->assertResult('sqrt(4)', new RationalNode(2, 1));

        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('sqrt(1/2)', 0);

        $this->assertResult('sqrt(225)', new RationalNode(15, 1));

        $this->assertResult('sqrt(7^6)', new RationalNode(7 * 7 * 7, 1));
    }

    /**
     * @return void
     */
    public function testCanEvaluateHyperbolicFunctions(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->assertResult('sinh(x)', 0);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCannotEvalauateUnknownFunction(): void
    {
        $f = new FunctionNode('sdf', [new RationalNode(1, 1)]);

        $this->expectException(UnknownFunctionException::class);
        $this->evaluate($f);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function testCannotEvaluateUnknownOperator(): void
    {
        $this->expectException(UnknownOperatorException::class);
        $node = new InfixExpressionNode('%', new RationalNode(1, 1), new VariableNode('x'));
        // We need to cheat here, since the ExpressionNode contructor already
        // throws an UnknownOperatorException when called with, say '%'
        // $node->setOperator('%');

        $this->evaluate($node);
    }

    /**
     * @return void
     */
    public function testUnknownException(): void
    {
        $this->expectException(UnknownOperatorException::class);
        $node = new InfixExpressionNode('%', null, null);
        $this->evaluate($node);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testParseRational(): void
    {
        $node = $this->evaluator->parseRational('1');
        static::assertEquals($node, new RationalNode(1, 1));

        $this->expectException(UnexpectedValueException::class);
        $this->evaluator->parseRational('1/2/3');
    }

    /**
     * @return RationalEvaluator
     * @throws DivisionByZeroException
     */
    public function testParseRational2(): RationalEvaluator
    {
        $this->expectException(UnexpectedValueException::class);
        return new RationalEvaluator(['x' => 'u/q']);
    }

    /**
     * @throws DivisionByZeroException
     */
    public function testCanSetVariables(): void
    {
        $eval = new RationalEvaluator(['x' => '1', 'y' => new RationalNode(2, 3)]);

        $value = ($this->parser->parse('x'))->accept($eval);
        static::assertEquals($value, new RationalNode(1, 1));

        $value = ($this->parser->parse('y'))->accept($eval);
        static::assertEquals($value, new RationalNode(2, 3));
    }

    /**
     * @return void
     */
    public function testCanFactor(): void
    {
        $factors = $this->evaluator::ifactor(51);
        static::assertEquals([3 => 1, 17 => 1], $factors);

        $factors = $this->evaluator::ifactor(25 * 13);
        static::assertEquals([5 => 2, 13 => 1], $factors);
    }
}
