<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class UnknowConstantExceptionTest
 */
class UnknowConstantExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testUnknownConstantException(): void
    {
        try {
            throw new UnknownConstantException('@');
        } catch (UnknownConstantException $e) {
            static::assertEquals('@', $e->getData());
            static::assertEquals('@', $e->getUnknownConstant());
            static::assertEquals('Unknown constant @ encountered.', $e->getMessage());
        }
    }
}
