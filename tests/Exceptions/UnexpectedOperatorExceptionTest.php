<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class UnexpectedOperatorExceptionTest
 */
class UnexpectedOperatorExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testSyntaxErrorException(): void
    {
        try {
            throw new UnexpectedOperatorException('+', '-');
        } catch (UnexpectedOperatorException $e) {
            static::assertEquals('Unexpected operator + passed, expected -', $e->getMessage());
            static::assertEquals('+', $e->getData());
            static::assertEquals('+', $e->getUnexpectedOperator());
        }
    }
}
