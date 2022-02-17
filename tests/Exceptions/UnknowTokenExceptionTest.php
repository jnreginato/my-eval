<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class UnknowTokenExceptionTest
 */
class UnknowTokenExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testUnknownTokenException(): void
    {
        try {
            throw new UnknownTokenException('@');
        } catch (UnknownTokenException $e) {
            static::assertEquals('@', $e->getData());
            static::assertEquals('@', $e->getUnknownToken());
            static::assertEquals('Unknown token @ encountered.', $e->getMessage());
        }
    }
}
