<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class LogarithmOfZeroExceptionTest
 */
class LogarithmOfZeroExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testDivisionByZeroException(): void
    {
        try {
            throw new LogarithmOfZeroException();
        } catch (LogarithmOfZeroException $e) {
            static::assertEquals('Logarithm of zero is undefined.', $e->getMessage());
            static::assertEquals('', $e->getData());
        }
    }
}
