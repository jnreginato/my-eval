<?php

declare(strict_types=1);

namespace MyEval\Extensions;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class MathTest
 */
class MathTest extends TestCase
{
    /**
     * @return void
     */
    public function testCanCalculateGreatestCommonDivisor(): void
    {
        static::assertSame(4, Math::gcd(8, 12));
        static::assertSame(4, Math::gcd(12, 8));
        static::assertSame(1, Math::gcd(12, 7));

        // Edge cases
        static::assertSame(5, Math::gcd(0, 5));
        static::assertSame(0, Math::gcd(0, 0));
        static::assertSame(-2, Math::gcd(2, -2));
        static::assertSame(2, Math::gcd(-2, -2));
    }

    /**
     * @return void
     */
    public function testCanProcessLogGamma(): void
    {
        static::assertEqualsWithDelta(857.9336698, Math::logGamma(200), 3e-7);
        static::assertEqualsWithDelta(log(120), Math::logGamma(6), 3e-9);
        static::assertEqualsWithDelta(log(120), Math::logGamma(6.0000000000000001), 3e-9);
        static::assertEqualsWithDelta(INF, Math::logGamma(0.00000000000000001), 3e-9);
        static::assertEqualsWithDelta(3.9578139676187, Math::logGamma(5.5), 3e-9);
    }

    /**
     * @return void
     */
    public function testReturnErrorOnProcessLogGammaNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Math::logGamma(-1);
    }

    /**
     * @return void
     */
    public function testCanCalculateFactorial(): void
    {
        static::assertSame(1, Math::factorial(0));
        static::assertSame(6, Math::factorial(3));
        static::assertSame(362880, Math::factorial(9));
    }

    /**
     * @return void
     */
    public function testEmitErrorOnCalculateFactorialWithNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Math::factorial(-1);
    }

    /**
     * @return void
     */
    public function testCanCalculateSemiFactorial(): void
    {
        static::assertSame(1, Math::semiFactorial(0));
        static::assertSame(1, Math::semiFactorial(1));
        static::assertSame(2, Math::semiFactorial(2));
        static::assertSame(3, Math::semiFactorial(3));
        static::assertSame(8, Math::semiFactorial(4));
        static::assertSame(15, Math::semiFactorial(5));
        static::assertSame(48, Math::semiFactorial(6));
        static::assertSame(105, Math::semiFactorial(7));
    }

    /**
     * @return void
     */
    public function testEmitErrorOnCalculateSemiFactorialWithNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Math::semiFactorial(-1);
    }
}
