<?php

declare(strict_types=1);

namespace MyEval\Extensions;

use InvalidArgumentException;

/**
 * Implementation of some arithmetic rules for real numbers.
 *
 * - the greatest common denominator;
 * - log gamma;
 * - factorial and
 * - semiFactorial
 */
class Math
{
    /**
     * Compute the greatest common denominator, using the Euclidean algorithm.
     *
     * Compute and return gcd($a, $b).
     *
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    public static function gcd(int $a, int $b): int
    {
        $sign = 1;

        if ($a < 0) {
            $sign = -$sign;
        }

        if ($b < 0) {
            $sign = -$sign;
        }

        while ($b !== 0) {
            $m = $a % $b;
            $a = $b;
            $b = $m;
        }

        return $sign * abs($a);
    }

    /**
     * Compute log(Gamma($a)) where $a is a positive real number.
     *
     * For large values of $a ($a > 171), use Stirling asymptotic expansion, otherwise use the Lanczos approximation.
     *
     * @param float $a
     *
     * @return float
     */
    public static function logGamma(float $a): float
    {
        if ($a < 0) {
            throw new InvalidArgumentException('Log gamma calls should be > 0.');
        }

        // Lanczos approximation with the given coefficients is accurate to 15 digits for 0 <= real(z) <= 171.
        if ($a >= 171) {
            return self::logStirlingApproximation($a);
        }

        return log(self::lanczosApproximation($a));
    }

    /**
     * Compute log(Gamma($x)) using Stirling asymptotic expansion.
     *
     * @param float $x
     *
     * @return float
     */
    private static function logStirlingApproximation(float $x): float
    {
        $t = 0.5 * log(2 * M_PI) - 0.5 * log($x) + $x * (log($x)) - $x;

        $x2 = $x * $x;
        $x3 = $x2 * $x;
        $x4 = $x3 * $x;

        $err_term = log(
            1 + (1.0 / (12 * $x)) + (1.0 / (288 * $x2)) - (139.0 / (51840 * $x3)) - (571.0 / (2488320 * $x4))
        );

        return $t + $err_term;
    }

    /**
     * Compute log(Gamma($x)) using Lanczos approximation.
     *
     * @param float $x
     *
     * @return float
     */
    private static function lanczosApproximation(float $x): float
    {
        $g = 7;
        $p = [
            0.99999999999980993,
            676.5203681218851,
            -1259.1392167224028,
            771.32342877765313,
            -176.61502916214059,
            12.507343278686905,
            -0.13857109526572012,
            9.9843695780195716e-6,
            1.5056327351493116e-7,
        ];

        if (abs($x - floor($x)) < 1e-16) {
            return self::processIntegerApproximation((int)$x);
        }

        --$x;
        $y = $p[0];
        for ($i = 1; $i < $g + 2; $i++) {
            $y = $y + $p[$i] / ($x + $i);
        }
        $t = $x + $g + 0.5;

        return sqrt(2 * M_PI) * exp((($x + 0.5) * log($t)) - $t) * $y;
    }

    /**
     * In the lanczos approximation if we're real close to an integer, let's just compute the factorial integer.
     *
     * @param int $x
     *
     * @return float
     */
    private static function processIntegerApproximation(int $x): float
    {
        if ($x >= 1) {
            return self::factorial($x - 1);
        }

        return INF;
    }

    /**
     * Compute factorial n! for an integer $n using iteration.
     *
     * @param int $num
     *
     * @return int
     */
    public static function factorial(int $num): int
    {
        if ($num < 0) {
            throw new InvalidArgumentException('Fatorial calls should be > 0.');
        }

        $returnVal = 1;
        for ($i = 1; $i <= $num; $i++) {
            $returnVal *= $i;
        }

        return $returnVal;
    }

    /**
     * Compute semi-factorial n!! for an integer $n using iteration.
     *
     * @param int $num
     *
     * @return int
     */
    public static function semiFactorial(int $num): int
    {
        if ($num < 0) {
            throw new InvalidArgumentException('Semi-factorial calls should be > 0.');
        }

        $returnVal = 1;
        while ($num >= 2) {
            $returnVal *= $num;
            $num       -= 2;
        }

        return $returnVal;
    }
}
