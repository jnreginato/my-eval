<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class UnknowVariableExceptionTest
 */
class UnknowVariableExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testUnknownVariableException(): void
    {
        try {
            throw new UnknownVariableException('@');
        } catch (UnknownVariableException $e) {
            static::assertEquals('@', $e->getData());
            static::assertEquals('@', $e->getVariable());
            static::assertEquals('Unknown variable @ encountered.', $e->getMessage());
        }
    }
}
