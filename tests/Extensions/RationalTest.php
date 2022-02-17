<?php

declare(strict_types=1);

namespace MyEval\Extensions;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\SyntaxErrorException;
use PHPUnit\Framework\TestCase;

/**
 * Class RationalTest
 */
class RationalTest extends TestCase
{
    /**
     * @return Rational
     * @throws DivisionByZeroException
     */
    public function testEmitErrorIfConstructWithZeroAsDenominator(): Rational
    {
        $this->expectException(DivisionByZeroException::class);
        return new Rational(1, 0);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanCreateRationalNumberFromFloat(): void
    {
        $x = Rational::fromFloat(0.33333333333);
        static::assertEquals('1/3', (string)$x);

        $x = Rational::fromFloat('0.33333333333');
        static::assertEquals('1/3', (string)$x);

        $x = Rational::fromFloat('0,33333333333');
        static::assertEquals('1/3', (string)$x);

        $x = Rational::fromFloat('-0.33333333333');
        static::assertEquals('-1/3', (string)$x);

        $x = Rational::fromFloat('0,0');
        static::assertEquals('0', (string)$x);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseAStringToARationalNumber(): void
    {
        $x = Rational::parse('3');
        static::assertEquals(3, $x->getNumerator());
        static::assertEquals(1, $x->getDenominator());
        static::assertEquals('3', (string)$x);

        $x = Rational::parse('1/2');
        static::assertEquals(1, $x->getNumerator());
        static::assertEquals(2, $x->getDenominator());
        static::assertEquals('1/2', (string)$x);

        $x = Rational::parse('-1/2');
        static::assertEquals(-1, $x->getNumerator());
        static::assertEquals(2, $x->getDenominator());
        static::assertEquals('-1/2', (string)$x);

        $x = Rational::parse('2/4');
        static::assertEquals(1, $x->getNumerator());
        static::assertEquals(2, $x->getDenominator());
        static::assertEquals('1/2', (string)$x);

        $x = Rational::parse('2/4', false);
        static::assertEquals(2, $x->getNumerator());
        static::assertEquals(4, $x->getDenominator());
        static::assertEquals('2/4', (string)$x);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanParseEmitFailure(): void
    {
        $this->expectException(SyntaxErrorException::class);
        Rational::parse('');

        $this->expectException(SyntaxErrorException::class);
        Rational::parse('sdf');
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testEmitExceptionOnRationalFromStringDivisionByZero(): void
    {
        $this->expectException(DivisionByZeroException::class);
        Rational::parse('1/0');
    }

    /**
     * @throws DivisionByZeroException
     */
    public function testEmitExceptionIfNan(): void
    {
        $this->expectException(SyntaxErrorException::class);
        Rational::parse('1/2/3');
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanDoAritmethic(): void
    {
        $x = new Rational(1, 2);
        $y = new Rational(2, 3);

        static::assertEquals(new Rational(7, 6), Rational::add($x, $y));
        static::assertEquals(new Rational(-1, 6), Rational::sub($x, $y));
        static::assertEquals(new Rational(1, 3), Rational::mul($x, $y));
        static::assertEquals(new Rational(3, 4), Rational::div($x, $y));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testCanDoAritmethicWithStrings(): void
    {
        $x = '1/2';
        $y = '2/3';

        static::assertEquals(new Rational(7, 6), Rational::add($x, $y));
        static::assertEquals(new Rational(-1, 6), Rational::sub($x, $y));
        static::assertEquals(new Rational(1, 3), Rational::mul($x, $y));
        static::assertEquals(new Rational(3, 4), Rational::div($x, $y));
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     */
    public function testCanSignedARationalNumber(): void
    {
        $positiveNumber = new Rational(2, 1);
        $signed         = $positiveNumber->signed();
        static::assertSame('+2', $signed);

        $positiveNumber = new Rational(2, 4);
        $signed         = $positiveNumber->signed();
        static::assertSame('+1/2', $signed);
    }

    /**
     * @return void
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public function testEmitErrorOnDivisionByZero(): void
    {
        $x = Rational::parse('1/2');
        $y = Rational::parse('0');

        $this->expectException(DivisionByZeroException::class);
        Rational::div($x, $y);
    }
}
