<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class SynatxErrorExceptionTest
 */
class SynatxErrorExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testSyntaxErrorException(): void
    {
        try {
            throw new SyntaxErrorException();
        } catch (SyntaxErrorException $e) {
            static::assertEquals('Syntax error.', $e->getMessage());
            static::assertEquals('', $e->getData());
        }
    }
}
