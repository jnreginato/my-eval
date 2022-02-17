<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class DivisionByZeroExceptionTest
 */
class DivisionByZeroExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testDivisionByZeroException(): void
    {
        try {
            throw new DivisionByZeroException();
        } catch (DivisionByZeroException $e) {
            static::assertEquals('Division by zero.', $e->getMessage());
            static::assertEquals('', $e->getData());
        }
    }
}
