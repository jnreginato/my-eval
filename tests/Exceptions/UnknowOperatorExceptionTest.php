<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class UnknowOperatorExceptionTest
 */
class UnknowOperatorExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testUnknownOperatorException(): void
    {
        try {
            throw new UnknownOperatorException('@');
        } catch (UnknownOperatorException $e) {
            static::assertEquals('@', $e->getData());
            static::assertEquals('@', $e->getUnknownOperator());
            static::assertEquals('Unknown operator @ encountered.', $e->getMessage());
        }
    }
}
