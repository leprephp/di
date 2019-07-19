<?php

/*
 * This file is part of the Lepre package.
 *
 * (c) Daniele De Nobili <danieledenobili@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Lepre\DI\Tests\Exception;

use Lepre\DI\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lepre\DI\Exception\NotFoundException
 */
class NotFoundExceptionTest extends TestCase
{
    public function testDefaultInitialization()
    {
        $e = new NotFoundException('service');

        $this->assertEquals('The service "service" does not exist.', $e->getMessage());
        $this->assertEquals(0, $e->getCode());
        $this->assertNull($e->getPrevious());
    }

    public function testInitializationHonorsCodeAndPrevious()
    {
        $previous = new \Exception();
        $e = new NotFoundException('service', 123, $previous);

        $this->assertEquals('The service "service" does not exist.', $e->getMessage());
        $this->assertEquals(123, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}
