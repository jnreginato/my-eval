<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class UnknowFunctionExceptionTest
 */
class UnknowFunctionExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testUnknownFunctionException(): void
    {
        try {
            throw new UnknownFunctionException('@');
        } catch (UnknownFunctionException $e) {
            static::assertEquals('@', $e->getData());
            static::assertEquals('@', $e->getUnknownFunction());
            static::assertEquals('Unknown function @ encountered.', $e->getMessage());
        }
    }
}
