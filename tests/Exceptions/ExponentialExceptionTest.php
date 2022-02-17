<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class ExponentialExceptionTest
 */
class ExponentialExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testDivisionByZeroException(): void
    {
        try {
            throw new ExponentialException();
        } catch (ExponentialException $e) {
            static::assertEquals('Zero raised to zero is undefined.', $e->getMessage());
            static::assertEquals('', $e->getData());
        }
    }
}
