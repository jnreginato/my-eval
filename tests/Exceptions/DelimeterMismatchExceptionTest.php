<?php

declare(strict_types=1);

namespace MyEval\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * Class DelimeterMismatchExceptionTest
 */
class DelimeterMismatchExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testParenthesisMismatchException(): void
    {
        try {
            throw new DelimeterMismatchException('(x+y');
        } catch (DelimeterMismatchException $e) {
            static::assertEquals('Unable to match delimiters.', $e->getMessage());
            static::assertEquals('(x+y', $e->getData());
        }
    }
}
