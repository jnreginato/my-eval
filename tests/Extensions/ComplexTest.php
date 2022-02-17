<?php

declare(strict_types=1);

namespace MyEval\Extensions;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\LogarithmOfZeroException;
use MyEval\Exceptions\SyntaxErrorException;
use PHPUnit\Framework\TestCase;

/**
 * Class ComplexTest
 */
class ComplexTest extends TestCase
{
    /**
     * @return void
     */
    public function testCanConstructComplexFromInteger(): void
    {
        $z = new Complex(2, 0);
        static::assertSame(2.0, $z->real);
        static::assertSame(0.0, $z->imaginary);
        static::assertSame('2', (string)$z);
    }

    /**
     * @return void
     */
    public function testCanConstructComplexFromFloat(): void
    {
        $z = new Complex(2.5, 0);
        static::assertSame(2.5, $z->real);
        static::assertSame(0.0, $z->imaginary);
        static::assertSame('5/2', (string)$z);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanCreateAComplexNumberFromString(): void
    {
        $z = Complex::create('1', '1/2');
        static::assertSame(1.0, $z->real);
        static::assertSame(0.5, $z->imaginary);
        static::assertSame('1+1/2i', (string)$z);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanCreateAComplexNumberFromInteger(): void
    {
        $z = Complex::create(1, 2);
        static::assertSame(1.0, $z->real);
        static::assertSame(2.0, $z->imaginary);
        static::assertSame('1+2i', (string)$z);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanCreateAComplexNumberFromFloat(): void
    {
        $z = Complex::create(1.5, 2.0);
        static::assertSame(1.5, $z->real);
        static::assertSame(2.0, $z->imaginary);
        static::assertSame('3/2+2i', (string)$z);
    }

    /**
     * @throws DivisionByZeroException
     */
    public function testCreateFailure(): void
    {
        $this->expectException(SyntaxErrorException::class);
        Complex::create('', '23');
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseFromString(): void
    {
        // Full complex number.
        $z = Complex::parse('2+5i');
        static::assertSame(2.0, $z->real);
        static::assertSame(5.0, $z->imaginary);
        static::assertSame('2+5i', (string)$z);

        // Unitary imaginary part.
        $z = Complex::parse('2+i');
        static::assertSame(2.0, $z->real);
        static::assertSame(1.0, $z->imaginary);
        static::assertSame('2+i', (string)$z);

        // Negative unitary imaginary part.
        $z = Complex::parse('2-i');
        static::assertSame(2.0, $z->real);
        static::assertSame(-1.0, $z->imaginary);
        static::assertSame('2-i', (string)$z);

        // Purely unary imaginary part (Real part missing).
        $z = Complex::parse('i');
        static::assertSame(0.0, $z->real);
        static::assertSame(1.0, $z->imaginary);
        static::assertSame('i', (string)$z);

        // Purely unary negative imaginary part (Real part missing).
        $z = Complex::parse('-i');
        static::assertSame(0.0, $z->real);
        static::assertSame(-1.0, $z->imaginary);
        static::assertSame('-i', (string)$z);

        // Purely imaginary (Real part missing).
        $z = Complex::parse('2i');
        static::assertSame(0.0, $z->real);
        static::assertSame(2.0, $z->imaginary);
        static::assertSame('2i', (string)$z);

        // Purely negative imaginary (Real part missing).
        $z = Complex::parse('-3i');
        static::assertSame(0.0, $z->real);
        static::assertSame(-3.0, $z->imaginary);
        static::assertSame('-3i', (string)$z);

        // Purely integer real part (Imaginary part missing).
        $z = Complex::parse('2');
        static::assertSame(2.0, $z->real);
        static::assertSame(0.0, $z->imaginary);
        static::assertSame('2', (string)$z);

        // Purely float real part (Imaginary part missing).
        $z = Complex::parse('0.2353578');
        static::assertSame(0.2353578, $z->real);
        static::assertSame(0.0, $z->imaginary);

        // Rational coefficients
        $z = Complex::parse('2/3+1/2i');
        static::assertSame(2 / 3, $z->real);
        static::assertSame(1 / 2, $z->imaginary);
        static::assertSame('2/3+1/2i', (string)$z);

        // Real coefficients, (note that numbers that can be identified with small fractions are printed as such)
        $z = Complex::parse('0.7-0.2i');
        static::assertSame(0.7, $z->real);
        static::assertSame(-0.2, $z->imaginary);
        static::assertSame('7/10-1/5i', (string)$z);

        // Empty string
        $z = Complex::parse('');
        static::assertSame(0.0, $z->real);
        static::assertSame(0.0, $z->imaginary);
        static::assertSame('0', (string)$z);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseFromInt(): void
    {
        $z = Complex::parse(2);

        static::assertSame(2.0, $z->real);
        static::assertSame(0.0, $z->imaginary);
        static::assertSame('2', (string)$z);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseFromFloat(): void
    {
        $z = Complex::parse(2.0);

        static::assertSame(2.0, $z->real);
        static::assertSame(0.0, $z->imaginary);
        static::assertSame('2', (string)$z);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseFromRational(): void
    {
        $z1 = Rational::parse('2/3');
        $z2 = Complex::parse($z1);

        static::assertSame(2 / 3, $z2->real);
        static::assertSame(0.0, $z2->imaginary);
        static::assertSame('2/3', (string)$z2);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseFromComplex(): void
    {
        $z1 = new Complex(1, 2);
        static::assertSame(1.0, $z1->real);
        static::assertSame(2.0, $z1->imaginary);

        $z2 = Complex::parse($z1);
        static::assertSame(1.0, $z2->real);
        static::assertSame(2.0, $z2->imaginary);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testParseFailure(): void
    {
        $this->expectException(SyntaxErrorException::class);
        Complex::parse('sdf');
    }

    /**
     * @return void
     */
    public function testCanComputeNonAnalytic(): void
    {
        $accuracy = 1e-9;
        $z1       = new Complex(1, 2);
        $z2       = new Complex(2, -1);
        $z3       = new Complex(2.5, 0);
        $z4       = new Complex(1 / 5, -2 / 5);

        static::assertEqualsWithDelta(1, $z1->real, $accuracy, 'r');
        static::assertEqualsWithDelta(2, $z1->imaginary, $accuracy, 'i');
        static::assertEqualsWithDelta(sqrt(5), $z1->abs(), $accuracy, 'abs');
        static::assertEqualsWithDelta(1.107148718, $z1->arg(), $accuracy, 'arg');

        static::assertEqualsWithDelta(2, $z2->real, $accuracy, 'r');
        static::assertEqualsWithDelta(-1, $z2->imaginary, $accuracy, 'i');
        static::assertEqualsWithDelta(sqrt(5), $z2->abs(), $accuracy, 'abs');
        static::assertEqualsWithDelta(-0.463647609, $z2->arg(), $accuracy, 'arg');

        static::assertEqualsWithDelta(2.5, $z3->real, $accuracy, 'r');
        static::assertEqualsWithDelta(0, $z3->imaginary, $accuracy, 'i');
        static::assertEqualsWithDelta(sqrt(6.25), $z3->abs(), $accuracy, 'abs');
        static::assertEqualsWithDelta(0, $z3->arg(), $accuracy, 'arg');

        static::assertEqualsWithDelta(0.2, $z4->real, $accuracy, 'r');
        static::assertEqualsWithDelta(-2 / 5, $z4->imaginary, $accuracy, 'i');
        static::assertEqualsWithDelta(sqrt(0.2), $z4->abs(), $accuracy, 'abs');
        static::assertEqualsWithDelta(-1.107148718, $z4->arg(), $accuracy, 'arg');
    }

    /**
     * @return void
     */
    public function testCanSignedAComplexNumber(): void
    {
        $positiveNumber = new Complex(2, 4);
        $signed         = $positiveNumber->signed();
        static::assertSame('+2+4i', $signed);

        $negativeNumber = new Complex(-2, 4);
        $signed         = $negativeNumber->signed();
        static::assertSame('-2+4i', $signed);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanDoAritmethic(): void
    {
        $z = new Complex(1, 2);
        $w = new Complex(2, -1);

        static::assertEquals(new Complex(3, 1), Complex::add($z, $w));
        static::assertEquals(new Complex(3, 2), Complex::add($z, 2));
        static::assertEquals(new Complex(4, -1), Complex::add(2, $w));

        static::assertEquals(new Complex(-1, 3), Complex::sub($z, $w));
        static::assertEquals(new Complex(-1, 2), Complex::sub($z, 2));
        static::assertEquals(new Complex(0, 1), Complex::sub(2, $w));

        static::assertEquals(new Complex(4, 3), Complex::mul($z, $w));
        static::assertEquals(new Complex(2, 4), Complex::mul($z, 2));
        static::assertEquals(new Complex(4, -2), Complex::mul(2, $w));

        static::assertEquals(new Complex(0, 1), Complex::div($z, $w));
        static::assertEquals(new Complex(0.5, 1), Complex::div($z, 2));
        static::assertEquals(new Complex(0.8, 0.4), Complex::div(2, $w));

        $this->expectException(DivisionByZeroException::class);
        Complex::div($z, new Complex(0, 0));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanComputePowers(): void
    {
        $z = new Complex(1, 2);

        static::assertEquals(new Complex(-3, 4), Complex::pow($z, 2));
        static::assertEquals(new Complex(-11, -2), Complex::pow($z, 3));
        static::assertEquals(new Complex(1 / 5, -2 / 5), Complex::pow($z, -1));
        static::assertEquals(new Complex(0.2291401860, 0.2381701151), Complex::pow($z, new Complex(0, 1)));
        static::assertEquals(new Complex(1, 0), Complex::pow($z, 0));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanComputeTranscendentals(): void
    {
        $z     = new Complex(1, 2);
        $p     = '1+2i';
        $delta = 1e-9;

        static::assertEqualsWithDelta(new Complex(1.272019650, 0.7861513778), Complex::sqrt($z), $delta, 'sqrt');
        static::assertEqualsWithDelta(new Complex(1.272019650, 0.7861513778), Complex::sqrt($p), $delta, 'sqrt');

        static::assertEqualsWithDelta(new Complex(-1.131204384, 2.471726672), Complex::exp($z), $delta, 'exp');
        static::assertEqualsWithDelta(new Complex(-1.131204384, 2.471726672), Complex::exp($p), $delta, 'exp');

        static::assertEqualsWithDelta(new Complex(0.8047189562, 1.107148718), Complex::log($z), $delta, 'log');
        static::assertEqualsWithDelta(new Complex(0.8047189562, 1.107148718), Complex::log($p), $delta, 'log');
        $this->expectException(LogarithmOfZeroException::class);
        Complex::log(0);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws LogarithmOfZeroException
     */
    public function testCanCalculateTrigonometry(): void
    {
        $z     = new Complex(1, 2);
        $p     = '1+2i';
        $delta = 1e-9;

        static::assertEqualsWithDelta(new Complex(3.165778513, 1.959601041), Complex::sin($z), $delta, 'sin');
        static::assertEqualsWithDelta(new Complex(3.165778513, 1.959601041), Complex::sin($p), $delta, 'sin');

        static::assertEqualsWithDelta(new Complex(2.032723007, -3.051897799), Complex::cos($z), $delta, 'cos');
        static::assertEqualsWithDelta(new Complex(2.032723007, -3.051897799), Complex::cos($p), $delta, 'cos');

        static::assertEqualsWithDelta(new Complex(0.03381282608, 1.014793616), Complex::tan($z), $delta, 'tan');
        static::assertEqualsWithDelta(new Complex(0.03381282608, 1.014793616), Complex::tan($p), $delta, 'tan');

        static::assertEqualsWithDelta(new Complex(0.03279775553, -0.9843292265), Complex::cot($z), $delta, 'cot');
        static::assertEqualsWithDelta(new Complex(0.03279775553, -0.9843292265), Complex::cot($p), $delta, 'cot');

        static::assertEqualsWithDelta(new Complex(0.4270785864, 1.528570919), Complex::arcsin($z), $delta, 'arcsin');
        static::assertEqualsWithDelta(new Complex(0.4270785864, 1.528570919), Complex::arcsin($p), $delta, 'arcsin');

        static::assertEqualsWithDelta(new Complex(1.143717740, -1.528570919), Complex::arccos($z), $delta, 'arccos');
        static::assertEqualsWithDelta(new Complex(1.143717740, -1.528570919), Complex::arccos($p), $delta, 'arccos');

        static::assertEqualsWithDelta(new Complex(1.338972522, 0.4023594781), Complex::arctan($z), $delta, 'arctan');
        static::assertEqualsWithDelta(new Complex(1.338972522, 0.4023594781), Complex::arctan($p), $delta, 'arctan');

        static::assertEqualsWithDelta(new Complex(0.2318238045, -0.4023594781), Complex::arccot($z), $delta, 'arccot');
        static::assertEqualsWithDelta(new Complex(0.2318238045, -0.4023594781), Complex::arccot($p), $delta, 'arccot');

        static::assertEqualsWithDelta(new Complex(-0.4890562590, 1.403119251), Complex::sinh($z), $delta, 'sinh');
        static::assertEqualsWithDelta(new Complex(-0.4890562590, 1.403119251), Complex::sinh($p), $delta, 'sinh');

        static::assertEqualsWithDelta(new Complex(-0.6421481247, 1.068607421), Complex::cosh($z), $delta, 'cosh');
        static::assertEqualsWithDelta(new Complex(-0.6421481247, 1.068607421), Complex::cosh($p), $delta, 'cosh');

        static::assertEqualsWithDelta(new Complex(1.166736257, -0.2434582012), Complex::tanh($z), $delta, 'tanh');
        static::assertEqualsWithDelta(new Complex(1.166736257, -0.2434582012), Complex::tanh($p), $delta, 'tanh');

        static::assertEqualsWithDelta(new Complex(1.469351744, 1.063440024), Complex::arsinh($z), $delta, 'arsinh');
        static::assertEqualsWithDelta(new Complex(1.469351744, 1.063440024), Complex::arsinh($p), $delta, 'arsinh');

        static::assertEqualsWithDelta(new Complex(1.528570919, 1.143717740), Complex::arcosh($z), $delta, 'arcosh');
        static::assertEqualsWithDelta(new Complex(1.528570919, 1.143717740), Complex::arcosh($p), $delta, 'arcosh');

        static::assertEqualsWithDelta(new Complex(0.1732867951, 1.178097245), Complex::artanh($z), $delta, 'artanh');
        static::assertEqualsWithDelta(new Complex(0.1732867951, 1.178097245), Complex::artanh($p), $delta, 'artanh');
    }

    /**
     * @return void
     */
    public function testCanExpressComplexAsString(): void
    {
        $z = new Complex(1, 0.5);
        static::assertSame('1+1/2i', (string)$z);

        $z = new Complex(1, 2);
        static::assertSame('1+2i', (string)$z);

        $z = new Complex(1.5, 2.0);
        static::assertSame('3/2+2i', (string)$z);

        $z = new Complex(0, +2);
        static::assertSame('2i', (string)$z);

        $z = new Complex(1 / 101, 2);
        static::assertSame('0.009901+2i', (string)$z);

        $z = new Complex(2, 1 / 101);
        static::assertSame('2+0.009901i', (string)$z);
    }
}
