<?php

namespace Fuelrod\Tests;

use Fuelrod\Exceptions\FuelrodException;
use PHPUnit\Framework\TestCase;

class FuelrodExceptionTest extends TestCase
{
    public function testExtendsBaseException(): void
    {
        $exception = new FuelrodException('Something went wrong', 422);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame('Something went wrong', $exception->getMessage());
        $this->assertSame(422, $exception->getCode());
    }

    public function testCanBeThrown(): void
    {
        $this->expectException(FuelrodException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(500);

        throw new FuelrodException('test error', 500);
    }
}
