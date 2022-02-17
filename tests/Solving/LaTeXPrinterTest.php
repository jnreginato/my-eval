<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\ComplexMathEval;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\RationalMathEval;
use PHPUnit\Framework\TestCase;

/**
 * Class LaTeXPrinterTest
 */
class LaTeXPrinterTest extends TestCase
{
    private RationalMathEval $parser;

    private LaTeXPrinter $printer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser  = new RationalMathEval();
        $this->printer = new LaTeXPrinter();

        parent::setUp();
    }

    /**
     * @param $input
     * @param $output
     *
     * @return void
     */
    private function assertResult($input, $output): void
    {
        $node   = $this->parser->parse($input);
        $result = $node->accept($this->printer);

        static::assertEquals($result, $output);
    }

    /**
     * @return void
     */
    public function testCanPrintNumber(): void
    {
        $this->assertResult('4', '4');
        $this->assertResult('-2', '-2');
        $this->assertResult('1.5', '1.5');
        $this->assertResult('3/4', '\frac{3}{4}');
    }

    /**
     * @return void
     */
    public function testCanPrintVariable(): void
    {
        $this->assertResult('x', 'x');
    }

    /**
     * @return void
     */
    public function testCanPrintConstant(): void
    {
        $this->assertResult('pi', '\pi{}');
        $this->assertResult('e', 'e');
        $this->assertResult('NAN', '\operatorname{NAN}');
        $this->assertResult('INF', '\infty{}');

        $complexParser = new ComplexMathEval();
        $tree          = $complexParser->parse('i');
        $result        = $tree->accept($this->printer);
        static::assertSame('i', $result);

        $node = new ConstantNode('xcv');
        $this->expectException(UnknownConstantException::class);
        $node->accept($this->printer);
    }

    /**
     * @return void
     */
    public function testCanPrintUnaryMinus(): void
    {
        $this->assertResult('-x', '-x');
        $this->assertResult('sin(-x)', '\sin(-x)');
        $this->assertResult('(-1)^k', '(-1)^k');
        $this->assertResult('-(x-1)', '-(x-1)');
        $this->assertResult('-(2/3)', '\frac{-2}{3}');
    }

    /**
     * @return void
     */
    public function testCanPrintSums(): void
    {
        $this->assertResult('x+y+z', 'x+y+z');
        $this->assertResult('x+y-z', 'x+y-z');
        $this->assertResult('x-y-z', 'x-y-z');
        $this->assertResult('x-y+z', 'x-y+z');
        $this->assertResult('-x-y-z', '-x-y-z');
        $this->assertResult('x+(-y)', 'x+(-y)');
        $this->assertResult('x+y+z', 'x+y+z');
        $this->assertResult('1+2x+3x^2', '1+2x+3x^2');
    }

    /**
     * @return void
     */
    public function testCanPrintProducts(): void
    {
        $this->assertResult('xyz', 'xyz');
        $this->assertResult('xy/z', '\frac{xy}{z}');
        $this->assertResult('x/yz', '\frac{x}{y}z');
        $this->assertResult('x/y/z', '\frac{\frac{x}{y}}{z}');
    }

    /**
     * @return void
     */
    public function testCanPrintExponentiation(): void
    {

        $this->assertResult('x^y^z', 'x^{y^z}');
        $this->assertResult('(x^y)^z', 'x^{yz}');

        $this->parser->setSimplifying(false);
        $this->assertResult('x^y^z', 'x^{y^z}');
        $this->assertResult('(x^y)^z', '{x^y}^z');
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     */
    public function testCanAddBraces(): void
    {
        $node   = new IntegerNode(4);
        $output = $this->printer->bracesNeeded($node);

        static::assertEquals('4', $output);

        $node   = new IntegerNode(-2);
        $output = $this->printer->bracesNeeded($node);

        static::assertEquals('{-2}', $output);

        $node   = new IntegerNode(12);
        $output = $this->printer->bracesNeeded($node);

        static::assertEquals('{12}', $output);

        $node   = new VariableNode('x');
        $output = $this->printer->bracesNeeded($node);

        static::assertEquals('x', $output);

        $node   = new ConstantNode('pi');
        $output = $this->printer->bracesNeeded($node);

        static::assertEquals('\pi{}', $output);

        $node   = $this->parser->parse('x+1');
        $output = $this->printer->bracesNeeded($node);

        static::assertEquals('{x+1}', $output);
    }

    /**
     * @return void
     */
    public function testCanPrintDivision(): void
    {
        $this->assertResult('1/2', '\frac{1}{2}');
        $this->assertResult('x/y', '\frac{x}{y}');
        $this->assertResult('4/2', '2');
        $this->assertResult('1/(sin(x)^2)', '\frac{1}{\sin(x)^2}');
    }

    /**
     * @return void
     */
    public function testCanPrintMultiplication(): void
    {
        $this->assertResult('sin(x)*x', '\sin(x)\cdot x');
        $this->assertResult('2*(x+4)', '2(x+4)');
        $this->assertResult('(x+1)*(x+2)', '(x+1)(x+2)');

        $this->parser->setSimplifying(false);
        $this->assertResult('2*3', '2\cdot 3');
        $this->assertResult('2*x', '2x');
        $this->assertResult('2*3^2', '2\cdot 3^2');
        $this->assertResult('2*(1/2)^2', '2(\frac{1}{2})^2');
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     */
    public function testCanPrintFunctions(): void
    {
        $this->assertResult('sin(x)', '\sin(x)');
        $this->assertResult('cos(x)', '\cos(x)');
        $this->assertResult('tan(x)', '\tan(x)');

        $this->assertResult('log(x)', '\log(x)');
        $this->assertResult('log(2x)', '\log(2x)');
        $this->assertResult('log(2+x)', '\log(2+x)');

        $this->assertResult('ln(x)', '\ln(x)');
        $this->assertResult('ln(2x)', '\ln(2x)');
        $this->assertResult('ln(2+x)', '\ln(2+x)');

        $this->assertResult('sqrt(x)', '\sqrt{x}');
        $this->assertResult('sqrt(x^2)', '\sqrt{x^2}');

        $this->assertResult('asin(x)', '\arcsin(x)');
        $this->assertResult('arsinh(x)', '\operatorname{arsinh}(x)');
    }

    /**
     * @return void
     */
    public function testCantDifferentiateAbs(): void
    {
        $this->assertResult('abs(x)', '\lvert x\rvert ');
    }

    /**
     * @return void
     * @throws NullOperandException
     * @throws UnknownOperatorException
     * @throws UnknownOperatorException
     */
    public function testEmitExceptionIfExpressionNullOperand(): void
    {
        $laTeXPrinter = new LaTeXPrinter();
        $node         = new InfixExpressionNode('+', null, null);
        $this->expectException(NullOperandException::class);
        $laTeXPrinter->visitInfixExpressionNode($node);
    }

    /**
     * @return void
     * @throws NullOperandException
     * @throws UnknownOperatorException
     */
    public function testEmitExceptionIfExpressionUnknowOperator(): void
    {
        $differantiator = new LaTeXPrinter();
        $node           = new InfixExpressionNode('~', 1, 1);
        $this->expectException(UnknownOperatorException::class);
        $differantiator->visitInfixExpressionNode($node);
    }

    public function testItCanPrintExponentialFunctions(): void
    {
        $this->assertResult('exp(x)', 'e^x');
        $this->assertResult('exp(2)', 'e^2');
        $this->assertResult('exp(2x)', 'e^{2x}');
        $this->assertResult('exp(x/2)', 'e^{x/2}');
        $this->assertResult('exp((x+1)/2)', 'e^{(x+1)/2}');
        $this->assertResult('exp(-2x)', 'e^{-2x}');
        $this->assertResult('exp(-2x+3)', 'e^{-2x+3}');
        $this->assertResult('exp(x+y+z)', 'e^{x+y+z}');
        $this->assertResult('exp(x^2)', '\exp(x^2)');
        $this->assertResult('exp(sin(x))', 'e^{\sin(x)}');
        $this->assertResult('exp(sin(x)cos(x))', '\exp(\sin(x)\cdot \cos(x))');
    }

    public function testItCanPrintPowers(): void
    {
        $this->assertResult('x^y', 'x^y');
        $this->assertResult('x^2', 'x^2');
        $this->assertResult('x^(2y)', 'x^{2y}');
        $this->assertResult('x^(1/2)', 'x^{1/2}');
        $this->assertResult('x^((x+1)/2)', 'x^{(x+1)/2}');
        $this->assertResult('x^((y+z)^2/(w+t))', 'x^{(y+z)^2/(w+t)}');
    }

    /**
     * @return void
     */
    public function testCanPrintFactorials(): void
    {
        $this->assertResult('3!', '3!');
        $this->assertResult('x!', 'x!');
        $this->assertResult('e!', 'e!');
        $this->assertResult('(x+y)!', '(x+y)!');
        $this->assertResult('(x+2)!', '(x+2)!');
        $this->assertResult('sin(x)!', '(\sin(x))!');
        $this->assertResult('(3!)!', '(3!)!');
    }

    /**
     * @return void
     */
    public function testCanPrintSemiFactorials(): void
    {
        $this->assertResult('3!!', '3!!');
        $this->assertResult('x!!', 'x!!');
        $this->assertResult('e!!', 'e!!');
        $this->assertResult('(x+y)!!', '(x+y)!!');
        $this->assertResult('(x+2)!!', '(x+2)!!');
        $this->assertResult('sin(x)!!', '(\sin(x))!!');
    }
}
