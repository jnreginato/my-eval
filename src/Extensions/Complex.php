<?php

declare(strict_types=1);

namespace MyEval\Extensions;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\LogarithmOfZeroException;
use MyEval\Exceptions\SyntaxErrorException;

use function is_float;
use function is_int;
use function preg_match;
use function trim;

/**
 * Implementation of complex number arithmetic with the standard transcendental functions.
 *
 * ## Example:
 *
 * $z = new Complex(3, 4);           // Creates the complex number 3+4i
 * $w = new Complex(-1, 1);          // Creates the complex number -1+i
 * $product = Complex::mul($z, $w)   // Computes the product (3+4i)(-1+i)
 */
class Complex
{
    /**
     * @param float $real      Real part.
     * @param float $imaginary Imaginary part.
     */
    public function __construct(
        public readonly float $real,
        public readonly float $imaginary
    ) {
    }

    /**
     * Create a complex number from its real and imaginary parts.
     *
     * @param float|string $real
     * @param float|string $imag
     *
     * @return Complex
     * @throws SyntaxErrorException If the string cannot be parsed.
     * @throws DivisionByZeroException If results a division by zero.
     */
    public static function create(float|string $real, float|string $imag): Complex
    {
        return new Complex(static::toFloat($real), static::toFloat($imag));
    }

    /**
     * Convert data to a complex number, if possible.
     *
     * @param float|string|Rational|Complex $value
     *
     * @return Complex
     * @throws SyntaxErrorException if the string cannot be parsed
     * @throws DivisionByZeroException If results a division by zero.
     */
    public static function parse(float|string|Rational|Complex $value): Complex
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_float($value)) {
            return new Complex($value, 0);
        }

        if ($value instanceof Rational) {
            return new Complex($value->getNumerator() / $value->getDenominator(), 0);
        }

        // Match complex numbers with an explicit i
        $matches = [];

        $valid = preg_match('#^([-,+])?([0-9/,.]*?)([-,+]?)([0-9/,.]*?)i$#', trim($value), $matches);

        if ($valid === 1) {
            $real = $matches[2];
            if ($real === '') {
                $matches[3] = $matches[1];
                $real       = '0';
            }
            $imaginary = $matches[4];
            if ($imaginary === '') {
                $imaginary = '1';
            }

            if ($matches[1] === '-') {
                $real = '-' . $real;
            }
            if ($matches[3] === '-') {
                $imaginary = '-' . $imaginary;
            }

            try {
                $a        = Rational::parse($real);
                $realPart = $a->getNumerator() / $a->getDenominator();
            } catch (SyntaxErrorException) {
                $realPart = static::parseReal($real);
            }

            try {
                $b             = Rational::parse($imaginary);
                $imaginaryPart = $b->getNumerator() / $b->getDenominator();
            } catch (SyntaxErrorException) {
                $imaginaryPart = static::parseReal($imaginary);
            }
        } else {
            // That failed, try matching a rational number
            try {
                $a             = Rational::parse($value);
                $realPart      = $a->getNumerator() / $a->getDenominator();
                $imaginaryPart = 0;
            } catch (SyntaxErrorException) {
                // Final attempt, try matching a real number
                $realPart      = static::parseReal($value);
                $imaginaryPart = 0;
            }
        }

        return new Complex($realPart, $imaginaryPart);
    }

    /**
     * Modulus (absolute value).
     *
     * Return the modulus of the complex number z=x+iy, i.e. sqrt(x^2 + y^2).
     *
     * @return float Modulus.
     */
    public function abs(): float
    {
        return hypot($this->real, $this->imaginary);
    }

    /**
     * Argument (principal value).
     *
     * Returns the principal argument of the complex number, i.e. a number t with -pi < t <= pi, such that z = rexp(i*t)
     * for some positive real r.
     *
     * @return float argument.
     */
    public function arg(): float
    {
        return atan2($this->imaginary, $this->real);
    }

    /**
     * Insert sign to number.
     *
     * @return string
     */
    public function signed(): string
    {
        $str = (string)($this);
        if ($str[0] !== '-') {
            return "+$str";
        }

        return $str;
    }

    /**
     * Add two complex numbers.
     *
     * Complex::add($z, $w) computes and returns $z+$w.
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     * @param float|string|Rational|Complex $w Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function add(float|string|Rational|Complex $z, float|string|Rational|Complex $w): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        if (!($w instanceof self)) {
            $w = static::parse($w);
        }

        return static::create($z->real + $w->real, $z->imaginary + $w->imaginary);
    }

    /**
     * Subtract two complex numbers.
     *
     * Complex::sub($z, $w) computes and returns $z-$w.
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     * @param float|string|Rational|Complex $w Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function sub(float|string|Rational|Complex $z, float|string|Rational|Complex $w): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        if (!($w instanceof self)) {
            $w = static::parse($w);
        }

        return static::create($z->real - $w->real, $z->imaginary - $w->imaginary);
    }

    /**
     * Multiply two complex numbers.
     *
     * Complex::mul($z, $w) computes and returns $z*$w.
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     * @param float|string|Rational|Complex $w Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function mul(float|string|Rational|Complex $z, float|string|Rational|Complex $w): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        if (!($w instanceof self)) {
            $w = static::parse($w);
        }

        return static::create(
            $z->real * $w->real - $z->imaginary * $w->imaginary,
            $z->real * $w->imaginary + $z->imaginary * $w->real
        );
    }

    /**
     * Divide two complex numbers.
     *
     * Complex::div($z, $w) computes and returns $z/$w.
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     * @param float|string|Rational|Complex $w Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function div(float|string|Rational|Complex $z, float|string|Rational|Complex $w): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        if (!($w instanceof self)) {
            $w = static::parse($w);
        }

        $d = $w->real * $w->real + $w->imaginary * $w->imaginary;

        if ($d === 0.0) {
            throw new DivisionByZeroException();
        }

        return static::create(
            ($z->real * $w->real + $z->imaginary * $w->imaginary) / $d,
            (-$z->real * $w->imaginary + $z->imaginary * $w->real) / $d
        );
    }

    /**
     * Powers of two complex numbers.
     *
     * Complex::pow($z, $w) computes and returns the principal value of $z^$w.
     *
     * @param int|float|string|Rational|Complex $z Complex, or parsable to Complex.
     * @param int|float|string|Rational|Complex $w Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function pow(int|float|string|Rational|Complex $z, int|float|string|Rational|Complex $w): Complex
    {
        // If exponent is an integer, compute the power using a square-and-multiply algorithm.
        if (is_int($w)) {
            return static::powi($z, $w);
        }

        // Otherwise, compute the principal branch: z^w = exp(wlog z)
        return static::exp(static::mul($w, static::log($z)));
    }

    /**
     * Integer power of a complex number.
     *
     * Complex::powi($z, $n) computes and returns $z^$n where $n is an integer.
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     * @param int                           $n
     *
     * @return Complex
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    private static function powi(float|string|Rational|Complex $z, int $n): Complex
    {
        if ($n < 0) {
            return static::div(1, static::powi($z, -$n));
        }

        if ($n === 0) {
            return new Complex(1, 0);
        }

        $y = new Complex(1, 0);
        while ($n > 1) {
            if ($n % 2 === 0) {
                $n /= 2;
            } else {
                $y = static::mul($z, $y);
                $n = (int)(($n - 1) / 2);
            }
            $z = static::mul($z, $z);
        }

        return static::mul($z, $y);
    }

    /**
     * Complex square root function.
     *
     * Complex::sqrt($z) computes and returns the principal branch of sqrt($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function sqrt(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $r     = sqrt($z->abs());
        $theta = $z->arg() / 2;

        return new Complex($r * cos($theta), $r * sin($theta));
    }

    /**
     * Complex exponential function.
     *
     * Complex::exp($z) computes and returns exp($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function exp(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $r = exp($z->real);

        return new Complex($r * cos($z->imaginary), $r * sin($z->imaginary));
    }

    /**
     * Complex logarithm function.
     *
     * Complex::log($z) computes and returns the principal branch of log($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function log(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $modulus = $z->abs();
        $theta   = $z->arg();

        if (!$modulus) {
            throw new LogarithmOfZeroException();
        }

        return new Complex(log($modulus), $theta);
    }

    /**
     * Complex sine function.
     *
     * Complex::sin($z) computes and returns sin($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function sin(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::create(sin($z->real) * cosh($z->imaginary), cos($z->real) * sinh($z->imaginary));
    }

    /**
     * Complex cosine function.
     *
     * Complex::cos($z) computes and returns cos($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function cos(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::create(cos($z->real) * cosh($z->imaginary), -sin($z->real) * sinh($z->imaginary));
    }

    /**
     * Complex tangent function.
     *
     * Complex::tan($z) computes and returns tan($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function tan(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $d = cos($z->real) * cos($z->real) + sinh($z->imaginary) * sinh($z->imaginary);

        return static::create(sin($z->real) * cos($z->real) / $d, sinh($z->imaginary) * cosh($z->imaginary) / $d);
    }

    /**
     * Complex cotangent function.
     *
     * Complex::cot($z) computes and returns cot($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function cot(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $d = sin($z->real) * sin($z->real) + sinh($z->imaginary) * sinh($z->imaginary);

        return static::create(sin($z->real) * cos($z->real) / $d, -sinh($z->imaginary) * cosh($z->imaginary) / $d);
    }

    /**
     * Complex inverse sine function.
     *
     * Complex::arcsin($z) computes and returns the principal branch of arcsin($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function arcsin(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $I    = new Complex(0, 1);
        $iz   = static::mul($z, $I);                                // iz
        $temp = static::sqrt(static::sub(1, static::mul($z, $z)));  // sqrt(1-z^2)

        return static::div(static::log(static::add($iz, $temp)), $I);
    }

    /**
     * Complex inverse cosine function.
     *
     * Complex::arccos($z) computes and returns the principal branch of arccos($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function arccos(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $I    = new Complex(0, 1);
        $temp = static::mul(static::sqrt(static::sub(1, static::mul($z, $z))), $I);

        return static::div(static::log(static::add($z, $temp)), $I);
    }

    /**
     * Complex inverse tangent function.
     *
     * Complex::arctan($z) computes and returns the principal branch of arctan($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function arctan(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $I    = new Complex(0, 1);
        $iz   = static::mul($z, $I);
        $w    = static::div(static::add(1, $iz), static::sub(1, $iz));
        $logw = static::log($w);

        return static::div($logw, new Complex(0, 2));
    }

    /**
     * Complex inverse cotangent function.
     *
     * Complex::arccot($z) computes and returns the principal branch of arccot($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function arccot(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::sub(M_PI / 2, static::arctan($z));
    }

    /**
     * Complex hyperbolic sine function.
     *
     * Complex::sinh($z) computes and returns sinh($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function sinh(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::create(sinh($z->real) * cos($z->imaginary), cosh($z->real) * sin($z->imaginary));
    }

    /**
     * Complex hyperbolic cosine function.
     *
     * Complex::cosh($z) computes and returns cosh($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function cosh(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::create(cosh($z->real) * cos($z->imaginary), sinh($z->real) * sin($z->imaginary));
    }

    /**
     * Complex hyperbolic tangent function.
     *
     * Complex::tanh($z) computes and returns tanh($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     */
    public static function tanh(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        $d = sinh($z->real) * sinh($z->real) + cos($z->imaginary) * cos($z->imaginary);

        return static::create(sinh($z->real) * cosh($z->real) / $d, sin($z->imaginary) * cos($z->imaginary) / $d);
    }

    /**
     * Complex inverse hyperbolic sine function.
     *
     * Complex::arsinh($z) computes and returns the principal branch of arsinh($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function arsinh(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::log(static::add($z, static::sqrt(static::add(1, static::mul($z, $z)))));
    }

    /**
     * Complex inverse hyperbolic cosine function.
     *
     * Complex::arcosh($z) computes and returns the principal branch of arcosh($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function arcosh(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::log(static::add($z, static::sqrt(static::add(-1, static::mul($z, $z)))));
    }

    /**
     * Complex inverse hyperbolic tangent function.
     *
     * Complex::artanh($z) computes and returns the principal branch of artanh($z).
     *
     * @param float|string|Rational|Complex $z Complex, or parsable to Complex.
     *
     * @return Complex
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws LogarithmOfZeroException
     */
    public static function artanh(float|string|Rational|Complex $z): Complex
    {
        if (!($z instanceof self)) {
            $z = static::parse($z);
        }

        return static::div(static::log(static::div(static::add(1, $z), static::sub(1, $z))), 2);
    }

    /**
     * Check whether a string represents a signed real number.
     *
     * @param string $value
     *
     * @return bool True if $value is a signed real number.
     */
    private static function isSignedReal(string $value): bool
    {
        return (bool)preg_match('~^-?\d+([,|.]\d+)?$~', $value);
    }

    /**
     * Convert string to floating point number, if possible.
     *
     * Decimal commas accepted.
     *
     * @param string $value
     *
     * @return float
     * @throws SyntaxErrorException If the string cannot be parsed.
     */
    private static function parseReal(string $value): float
    {
        if ($value === '') {
            return 0;
        }

        $x = str_replace(',', '.', $value);

        if (static::isSignedReal($x)) {
            return (float)$x;
        }

        throw new SyntaxErrorException();
    }

    /**
     * Convert data to a floating point number, if possible.
     *
     * @param int|float|string $value
     *
     * @return float
     * @throws SyntaxErrorException If the string cannot be parsed.
     * @throws DivisionByZeroException If results a division by zero.
     */
    private static function toFloat(int|float|string $value): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        $rational = Rational::parse($value);
        return $rational->getNumerator() / $rational->getDenominator();
    }

    /**
     * String representation of a complex number.
     *
     * @return string
     * @throws DivisionByZeroException
     */
    public function __toString(): string
    {
        $realAsRational = Rational::fromFloat($this->real);
        if ($realAsRational->getDenominator() <= 100) {
            $real = (string)$realAsRational;
        } else {
            $real = sprintf('%f', $this->real);
        }

        $imagAsRational = Rational::fromFloat($this->imaginary);
        if ($imagAsRational->getDenominator() <= 100) {
            $imag = $imagAsRational->signed();
        } else {
            $imag = sprintf('%+f', $this->imaginary);
        }

        if ($this->imaginary === 0.0) {
            return $real;
        }

        if ($this->real === 0.0) {
            if ($this->imaginary === 1.0) {
                return 'i';
            }
            if ($this->imaginary === -1.0) {
                return '-i';
            }
            if ($imag[0] === '+') {
                $imag = substr($imag, 1);
            }
            return "${imag}i";
        }

        $imag = $this->imaginary === +1.0 ? '+' : $imag;
        $imag = $this->imaginary === -1.0 ? '-' : $imag;

        return "$real${imag}i";
    }
}
