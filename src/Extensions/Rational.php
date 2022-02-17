<?php

declare(strict_types=1);

namespace MyEval\Extensions;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\SyntaxErrorException;

use function count;
use function is_string;

/**
 * Implementation of rational number arithmetic.
 *
 * ## Example:
 *
 * $a = new Rational(1, 4);     // creates the rational number 1/4
 * $b = new Rational(2, 3);     // creates the rational number 2/3
 * sum = Rational::add($a, $b)  // computes the sum 1/4 + 2/3
 */
class Rational
{
    /**
     * @var int $p Numerator.
     */
    private int $p;

    /**
     * @var int $q Denominator.
     */
    private int $q;

    /**
     * Constuctor for Rational number class.
     *
     * $r = new Rational(2, 4)         // creates 1/2
     * $r = new Rational(2, 4, false)  // creates 2/4
     *
     * @param int  $p         Numerator.
     * @param int  $q         Denominator.
     * @param bool $normalize If true, store in normalized form, i.e. positive denominator and gcd($p, $q) = 1.
     *
     * @throws DivisionByZeroException
     */
    public function __construct(int $p, int $q, bool $normalize = true)
    {
        $this->p = $p;
        $this->q = $q;

        if ($q === 0) {
            throw new DivisionByZeroException();
        }

        if ($normalize) {
            $this->normalize();
        }
    }

    /**
     * Make sure the denominator is positive and that the numerator and denominator have no common factors.
     */
    private function normalize(): void
    {
        $gcd = Math::gcd($this->p, $this->q);

        $this->p /= $gcd;
        $this->q /= $gcd;

        if ($this->q < 0) {
            $this->p = -$this->p;
            $this->q = -$this->q;
        }
    }

    /**
     * Convert float to Rational.
     *
     * Convert float to a continued fraction, with prescribed accuracy.
     *
     * @param float|string $float
     * @param float        $tolerance
     *
     * @return Rational
     * @throws DivisionByZeroException
     */
    public static function fromFloat(float|string $float, float $tolerance = 1e-7): Rational
    {
        if (is_string($float) && preg_match('~^-?\d+([,|.]\d+)?$~', $float)) {
            $float = (float)str_replace(',', '.', $float);
        }

        if ($float === 0.0) {
            return new Rational(0, 1);
        }

        $negative = ($float < 0);
        if ($negative) {
            $float = (float)abs($float);
        }

        $num1    = 1;
        $num2    = 0;
        $den1    = 0;
        $den2    = 1;
        $oneOver = 1 / $float;
        do {
            $oneOver = 1 / $oneOver;
            $floor   = floor($oneOver);
            $aux     = $num1;
            $num1    = $floor * $num1 + $num2;
            $num2    = $aux;
            $aux     = $den1;
            $den1    = $floor * $den1 + $den2;
            $den2    = $aux;
            $oneOver -= $floor;
        } while (abs($float - $num1 / $den1) > $float * $tolerance);

        if ($negative) {
            $num1 *= -1;
        }

        return new Rational((int)$num1, (int)$den1);
    }

    /**
     * Convert $value to Rational.
     *
     * @param string|Rational $value
     * @param bool            $normalize
     *
     * @return Rational
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public static function parse(string|Rational $value, bool $normalize = true): Rational
    {
        if ($value === '') {
            throw new SyntaxErrorException();
        }

        $numbers = [];

        if ($value instanceof self) {
            $numbers = [(string)$value->p, (string)$value->q];
        }

        if (is_string($value)) {
            $numbers = explode('/', $value);
        }

        switch (count($numbers)) {
            case 1:
                $p = static::isSignedInteger($numbers[0]) ? (int)$numbers[0] : NAN;
                $q = 1;
                break;
            case 2:
                $p = static::isSignedInteger($numbers[0]) ? (int)$numbers[0] : NAN;
                $q = static::isInteger($numbers[1]) ? (int)$numbers[1] : NAN;
                break;
            default:
                $p = NAN;
                $q = NAN;
                break;
        }

        if (is_nan($p) || is_nan($q)) {
            throw new SyntaxErrorException();
        }

        return new Rational($p, $q, $normalize);
    }

    /**
     * Get a numerator of the Rational number.
     *
     * @return int
     */
    public function getNumerator(): int
    {
        return $this->p;
    }

    /**
     * Get a denominator of the Rational number.
     *
     * @return int
     */
    public function getDenominator(): int
    {
        return $this->q;
    }

    /**
     * Add two rational numbers.
     *
     * Rational::add($x, $y) computes and returns $x+$y.
     *
     * @param string|Rational $x Rational, or parsable to Rational.
     * @param string|Rational $y Rational, or parsable to Rational.
     *
     * @return Rational
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function add(string|Rational $x, string|Rational $y): Rational
    {
        $rational1 = static::parse($x);
        $rational2 = static::parse($y);

        $resp = $rational1->p * $rational2->q + $rational1->q * $rational2->p;
        $resq = $rational1->q * $rational2->q;

        return new Rational($resp, $resq);
    }

    /**
     * Subtract two rational numbers.
     *
     * Rational::sub($x, $y) computes and returns $x-$y.
     *
     * @param string|Rational $x Rational, or parsable to Rational.
     * @param string|Rational $y Rational, or parsable to Rational.
     *
     * @return Rational
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function sub(string|Rational $x, string|Rational $y): Rational
    {
        $rational1 = static::parse($x);
        $rational2 = static::parse($y);

        $resp = $rational1->p * $rational2->q - $rational1->q * $rational2->p;
        $resq = $rational1->q * $rational2->q;

        return new Rational($resp, $resq);
    }

    /**
     * Multiply two rational numbers.
     *
     * Rational::mul($x, $y) computes and returns $x*$y.
     *
     * @param string|Rational $x Rational, or parsable to Rational.
     * @param string|Rational $y Rational, or parsable to Rational.
     *
     * @return Rational
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public static function mul(string|Rational $x, string|Rational $y): Rational
    {
        $rational1 = static::parse($x);
        $rational2 = static::parse($y);

        $resp = $rational1->p * $rational2->p;
        $resq = $rational1->q * $rational2->q;

        return new Rational($resp, $resq);
    }

    /**
     * Add two rational numbers.
     *
     * Rational::div($x, $y) computes and returns $x/$y.
     *
     * @param string|Rational $x Rational, or parsable to Rational.
     * @param string|Rational $y Rational, or parsable to Rational.
     *
     * @return Rational
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     */
    public static function div(string|Rational $x, string|Rational $y): Rational
    {
        $rational1 = static::parse($x);
        $rational2 = static::parse($y);

        if ($rational2->p === 0) {
            throw new DivisionByZeroException();
        }

        $resp = $rational1->p * $rational2->q;
        $resq = $rational1->q * $rational2->p;

        return new Rational($resp, $resq);
    }

    /**
     * Convert rational number to string, adding a '+' if the number is positive.
     *
     * @return string
     */
    public function signed(): string
    {
        if ($this->q === 1) {
            return sprintf('%+d', $this->p);
        }

        return sprintf('%+d/%d', $this->p, $this->q);
    }

    /**
     * Test whether a string represents a positive integer.
     *
     * @param $value
     *
     * @return bool
     */
    private static function isInteger($value): bool
    {
        return (bool)preg_match('~^\d+$~', $value);
    }

    /**
     * Test whether a string represents a signed integer.
     *
     * @param $value
     *
     * @return bool
     */
    private static function isSignedInteger($value): bool
    {
        return (bool)preg_match('~^-?\d+$~', $value);
    }

    /**
     * Convert Rational to string.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->q === 1) {
            return (string)$this->p;
        }

        return "$this->p/$this->q";
    }
}
