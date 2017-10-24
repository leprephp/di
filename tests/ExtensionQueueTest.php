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

namespace Lepre\Component\DI\Tests;

use Lepre\Component\DI\Container;
use Lepre\Component\DI\ExtensionQueue;
use Lepre\Component\DI\Tests\Fixtures\Service;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lepre\Component\DI\ExtensionQueue
 */
class ExtensionQueueTest extends TestCase
{
    public function testQueue()
    {
        /** @var Container $container */
        $container = $this->createMock(Container::class);

        $queue = new ExtensionQueue($container);

        $queue->add(
            function (Service $service) {
                $service->concatenateParameter('a');
            }
        );

        $queue->add(
            function (Service $service) {
                $service->concatenateParameter('b');

                // Test the return value
                return $service;
            }
        );

        $queue->add(
            function (Service $service) {
                $service->concatenateParameter('c');
            }
        );

        /** @var Service $service */
        $service = $queue->getService(new Service());

        $this->assertEquals('abc', $service->getParameter());
    }
}
