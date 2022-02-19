<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\ComplexMathEval;
use MyEval\Exceptions\DelimeterMismatchException;
use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownTokenException;
use MyEval\LogicEval;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\RationalMathEval;
use PHPUnit\Framework\TestCase;

/**
 * Class ASCIIPrinterTest
 */
class ASCIIPrinterTest extends TestCase
{
    private RationalMathEval $parser;

    private ASCIIPrinter $printer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser  = new RationalMathEval();
        $this->printer = new ASCIIPrinter();

        parent::setUp();
    }

    /**
     * @param $input
     * @param $output
     *
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    private function assertResult($input, $output): void
    {
        $tree   = $this->parser->parse($input);
        $result = $tree->accept($this->printer);

        static::assertSame($result, $output);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintNumber(): void
    {
        $this->assertResult('4', '4');
        $this->assertResult('-2', '-2');
        $this->assertResult('1.5', '1.5');
        $this->assertResult('2/3', '2/3');
        $this->assertResult('4/6', '2/3');
        $this->assertResult('-1/2', '-1/2');
        $this->assertResult('4/2', '2');
        $this->assertResult('1/2+1/2', '1');
        $this->assertResult('1/(-2)+1/2', '0');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintVariable(): void
    {
        $this->assertResult('x', 'x');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintConstant(): void
    {
        $this->assertResult('pi', 'pi');
        $this->assertResult('e', 'e');
        $this->assertResult('NAN', 'NAN');
        $this->assertResult('INF', 'INF');

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
    public function testCanPrintBoolean(): void
    {
        static::assertSame('TRUE', (new BooleanNode('true'))->accept($this->printer));
        static::assertSame('FALSE', (new BooleanNode('false'))->accept($this->printer));
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintAdditionAndSubtraction(): void
    {
        $this->assertResult('x+1', 'x+1');
        $this->assertResult('x+y', 'x+y');
        $this->assertResult('x+y+z', 'x+y+z');
        $this->assertResult('x+y-z', 'x+y-z');
        $this->assertResult('x-y-z', 'x-y-z');
        $this->assertResult('x-y+z', 'x-y+z');
        $this->assertResult('-x-y-z', '-x-y-z');
        $this->assertResult('x+(-y)', 'x+(-y)');
        $this->assertResult('x+y+z', 'x+y+z');
        $this->assertResult('1+2x+3x^2', '1+2*x+3*x^2');
        $this->assertResult('1-(-1)*x', '1-(-1)*x');
        $this->assertResult('1-(-1)*x', '1-(-1)*x');
        $this->assertResult('x*(-1)+(-2)*(-x)', 'x*(-1)+(-2)*(-x)');
        $this->assertResult('x*(-1)-(-2)*(-x)', 'x*(-1)-(-2)*(-x)');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintUnaryMinus(): void
    {
        $this->assertResult('-x', '-x');
        $this->assertResult('1+(-x)', '1+(-x)');
        $this->assertResult('1+(-2)', '-1');
        $this->assertResult('(-1)^k', '(-1)^k');
        $this->assertResult('(-1/2)^k', '(-1/2)^k');
        $this->assertResult('-(x-1)', '-(x-1)');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintMultiplication(): void
    {
        $this->assertResult('sin(x)*x', 'sin(x)*x');
        $this->assertResult('2(x+4)', '2*(x+4)');
        $this->assertResult('(x+1)(x+2)', '(x+1)*(x+2)');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintDivision(): void
    {
        $this->assertResult('x/y', 'x/y');
        $this->assertResult('x/(y+z)', 'x/(y+z)');
        $this->assertResult('(x+y)/(y+z)', '(x+y)/(y+z)');
        $this->assertResult('(x+sin(x))/2', '(x+sin(x))/2');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintMultiplicationAndDivision(): void
    {
        $this->assertResult('x*y/z', 'x*y/z');
        $this->assertResult('x/y*z', 'x/y*z');
        $this->assertResult('x*y/(z*w)', 'x*y/(z*w)');
        $this->assertResult('x*y/(z+w)', 'x*y/(z+w)');
        $this->assertResult('x*y/(z-w)', 'x*y/(z-w)');
        $this->assertResult('(x+y)/(z-w)', '(x+y)/(z-w)');
        $this->assertResult('x*y/(z^w)', 'x*y/z^w');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintExponentiation(): void
    {
        $this->assertResult('x^2', 'x^2');
        $this->assertResult('x^(2/3)', 'x^(2/3)');
        $this->assertResult('(1/2)^k', '(1/2)^k');
        $this->assertResult('x^(y+z)', 'x^(y+z)');
        $this->assertResult('x^(y+z)', 'x^(y+z)');

        $this->assertResult('x^y^z', 'x^y^z');
        $this->assertResult('(x^y)^z', 'x^(y*z)');

        $this->parser->setSimplifying(false);
        $this->assertResult('x^y^z', 'x^y^z');
        $this->assertResult('(x^y)^z', '(x^y)^z');
        $this->parser->setSimplifying(true);
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintLogicalOperators(): void
    {
        $logicParser = new LogicEval();

        $tree = $logicParser->parse('x = 2');
        static::assertSame('x=2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x > 2');
        static::assertSame('x>2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x < 2');
        static::assertSame('x<2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x <> 2');
        static::assertSame('x<>2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x >= 2');
        static::assertSame('x>=2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x <= 2');
        static::assertSame('x<=2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x > 1 AND x < 2');
        static::assertSame('x>1 AND x<2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x > 1 && x < 2');
        static::assertSame('x>1 AND x<2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x > 1 OR y < 2');
        static::assertSame('x>1 OR y<2', $tree->accept($this->printer));

        $tree = $logicParser->parse('x > 1 || y < 2');
        static::assertSame('x>1 OR y<2', $tree->accept($this->printer));
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintFunctions(): void
    {
        $this->assertResult('sin(x)', 'sin(x)');
        $this->assertResult('(2+sin(x))/(1-1/2)', '(2+sin(x))/(1/2)');
        $this->assertResult('cos(x)', 'cos(x)');
        $this->assertResult('tan(x)', 'tan(x)');

        $this->assertResult('exp(x)', 'exp(x)');

        $this->assertResult('log(x)', 'log(x)');
        $this->assertResult('log(2+x)', 'log(2+x)');
        $this->assertResult('ln(x)', 'ln(x)');
        $this->assertResult('ln(2+x)', 'ln(2+x)');

        $this->assertResult('sqrt(x)', 'sqrt(x)');
        $this->assertResult('sqrt(x^2)', 'sqrt(x^2)');

        $this->assertResult('asin(x)', 'arcsin(x)');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintFactorials(): void
    {
        $this->assertResult('3!', '3!');
        $this->assertResult('x!', 'x!');
        $this->assertResult('e!', 'e!');
        $this->assertResult('(x+y)!', '(x+y)!');
        $this->assertResult('(x+2)!', '(x+2)!');
        $this->assertResult('sin(x)!', '(sin(x))!');
        $this->assertResult('(3!)!', '(3!)!');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintSemiFactorials(): void
    {
        $this->assertResult('3!!', '3!!');
        $this->assertResult('x!!', 'x!!');
        $this->assertResult('e!!', 'e!!');
        $this->assertResult('(x+y)!!', '(x+y)!!');
        $this->assertResult('(x+2)!!', '(x+2)!!');
        $this->assertResult('sin(x)!!', '(sin(x))!!');
    }

    /**
     * @return void
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws UnknownTokenException
     */
    public function testCanPrintIfOperator(): void
    {
        $logicParser = new LogicEval();

        $tree = $logicParser->parse('if (x) {1} else {0}');
        static::assertSame('if (x) {1} else {0}', $tree->accept($this->printer));
    }
}
