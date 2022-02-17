<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class NullOperandExceptionTest
 */
class NullOperandExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testSyntaxErrorException(): void
    {
        try {
            throw new NullOperandException();
        } catch (NullOperandException $e) {
            static::assertEquals('Null operand found.', $e->getMessage());
            static::assertEquals('', $e->getData());
        }
    }
}
