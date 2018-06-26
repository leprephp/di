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

namespace Lepre\DI\Tests;

use Lepre\DI\Container;
use Lepre\DI\ExtensionQueue;
use Lepre\DI\Tests\Fixtures\Service;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lepre\DI\ExtensionQueue
 */
class ExtensionQueueTest extends TestCase
{
    public function testQueue()
    {
        $queue = new ExtensionQueue(
            new Container()
        );

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
