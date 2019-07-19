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
use Lepre\DI\Exception\FrozenContainerException;
use Lepre\DI\Exception\NotFoundException;
use Lepre\DI\Tests\Fixtures\Invokable;
use Lepre\DI\Tests\Fixtures\Service;
use Lepre\DI\Tests\Fixtures\ServiceProvider;
use Lepre\DI\Tests\Fixtures\ServiceWithDependencies;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers \Lepre\DI\Container
 */
final class ContainerTest extends TestCase
{
    public function testPsrContainer()
    {
        $container = new Container();

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testPsrNotFoundException()
    {
        $this->expectException(NotFoundExceptionInterface::class);

        $container = new Container();
        $container->get('undefined');
    }

    public function testServices()
    {
        $container = new Container();

        $this->assertFalse($container->has('service'));

        $container->set('service', 'value');

        $this->assertTrue($container->has('service'));
        $this->assertEquals('value', $container->get('service'));
    }

    public function testSetReturnsTheContainer()
    {
        $container = new Container();
        $this->assertSame($container, $container->set('service', 'value'));
    }

    /**
     * @covers \Lepre\DI\Exception\NotFoundException
     */
    public function testGetCheckIfKeyIsPresent()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The service "undefined" does not exist.');

        $container = new Container();
        $container->get('undefined');
    }

    public function testGetHonorsNullValues()
    {
        $container = new Container();
        $container->set('service', null);

        $this->assertNull($container->get('service'));
    }

    public function testGetHonorsCallable()
    {
        $container = new Container();

        $container->set(
            'service',
            function () {
                return new Service();
            }
        );

        $this->assertInstanceOf(Service::class, $container->get('service'));
        $this->assertSame($container->get('service'), $container->get('service'));
    }

    public function testGetHonorsInvokableServices()
    {
        $container = new Container();

        $container->set('service', new Fixtures\Invokable());

        $this->assertInstanceOf(Service::class, $container->get('service'));
        $this->assertSame($container->get('service'), $container->get('service'));
    }

    public function testGetHonorsNestedCallable()
    {
        $container = new Container();

        $container->set(
            'service',
            function () {
                return function () {};
            }
        );

        $this->assertTrue(is_callable($container->get('service')));
        $this->assertSame($container->get('service'), $container->get('service'));
    }

    public function testNestedDependency()
    {
        $container = new Container();

        $container->set(
            'service1',
            function () {
                return new Fixtures\Service();
            }
        );

        $container->set(
            'service2',
            function () {
                return new Fixtures\Invokable();
            }
        );

        $container->set(
            'service3',
            function (Container $di) {
                return new Fixtures\ServiceWithDependencies($di->get('service1'), $di->get('service2'));
            }
        );

        $this->assertSame($container->get('service1'), $container->get('service3')->foo);
        $this->assertSame($container->get('service2'), $container->get('service3')->bar);

        $this->assertSame($container->get('service1'), $container->get('service1'));
        $this->assertSame($container->get('service2'), $container->get('service2'));
        $this->assertSame($container->get('service3'), $container->get('service3'));
    }

    public function testAlias()
    {
        $container = new Container();

        $this->assertFalse($container->has('original'));
        $this->assertFalse($container->has('alias'));

        $container->set(
            'original',
            function () {
                return new Service();
            }
        );

        $container->alias('alias', 'original');

        $this->assertTrue($container->has('original'));
        $this->assertTrue($container->has('alias'));
        $this->assertSame($container->get('original'), $container->get('alias'));
        $this->assertSame($container->raw('original'), $container->raw('alias'));
        $this->assertInstanceOf(Service::class, $container->getNew('alias'));
    }

    public function testAliasReturnsTheContainer()
    {
        $container = new Container();
        $container->set('original', 'value');

        $this->assertSame($container, $container->alias('alias', 'original'));
    }

    public function testCyclicAlias()
    {
        $container = new Container();

        $this->assertFalse($container->has('original'));
        $this->assertFalse($container->has('alias1'));
        $this->assertFalse($container->has('alias2'));

        $container->alias('alias1', 'original');
        $container->alias('alias2', 'alias1');

        $container->set(
            'original',
            function () {
                return new Service();
            }
        );

        $this->assertTrue($container->has('original'));
        $this->assertTrue($container->has('alias1'));
        $this->assertTrue($container->has('alias2'));

        $this->assertSame($container->get('original'), $container->get('alias1'));
        $this->assertSame($container->get('original'), $container->get('alias2'));
    }

    public function testSetAnAlreadyAliasedService()
    {
        $container = new Container();

        $this->assertFalse($container->has('original'));
        $this->assertFalse($container->has('alias'));

        $container->alias('alias', 'original');

        $this->assertFalse($container->has('original'));
        $this->assertFalse($container->has('alias'));

        $container->set(
            'original',
            function () {
                return new Service();
            }
        );

        $this->assertTrue($container->has('original'));
        $this->assertTrue($container->has('alias'));
        $this->assertSame($container->get('original'), $container->get('alias'));
    }

    public function testOverrideAnAlias()
    {
        $container = new Container();

        $this->assertFalse($container->has('original'));
        $this->assertFalse($container->has('alias'));

        $container->set(
            'original',
            function () {
                return new Service();
            }
        );

        $this->assertTrue($container->has('original'));
        $this->assertFalse($container->has('alias'));

        $container->alias('alias', 'original');

        $this->assertTrue($container->has('original'));
        $this->assertTrue($container->has('alias'));
        $this->assertSame($container->get('original'), $container->get('alias'));

        $container->set(
            'alias',
            function () {
                return new Service();
            }
        );

        $this->assertTrue($container->has('original'));
        $this->assertTrue($container->has('alias'));
        $this->assertNotSame($container->get('original'), $container->get('alias'));
    }

    public function testGetNew()
    {
        $container = new Container();

        $container->set(
            'callable',
            function () {
                return new Service();
            }
        );

        $this->assertInstanceOf(Service::class, $container->getNew('callable'));
        $this->assertNotSame($container->getNew('callable'), $container->getNew('callable'));

        $service = [1, 2, 3];
        $container->set('array', $service);
        $this->assertEquals($service, $container->getNew('array'));
    }

    /**
     * @covers \Lepre\DI\Exception\NotFoundException
     */
    public function testGetNewValidatesKeyIsPresent()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The service "undefined" does not exist.');

        $container = new Container();
        $container->getNew('undefined');
    }

    public function testRaw()
    {
        $container = new Container();

        $service = function () {
            return new Service();
        };

        $container->set('service', $service);

        $this->assertSame($service, $container->raw('service'));
    }

    public function testRawHonorsNullValues()
    {
        $container = new Container();
        $container->set('service', null);
        $this->assertNull($container->raw('service'));
    }

    /**
     * @covers \Lepre\DI\Exception\NotFoundException
     */
    public function testRawValidatesKeyIsPresent()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The service "undefined" does not exist.');

        $container = new Container();
        $container->raw('undefined');
    }

    public function testExtend()
    {
        $container = new Container();

        $container->set(
            'service',
            function () {
                return new Service();
            }
        );

        $container->extend(
            'service',
            function (Service $service) {
                $service->setParameter('test parameter');
            }
        );

        $this->assertEquals('test parameter', $container->get('service')->getParameter());
    }

    public function testExtendReturnsTheContainer()
    {
        $container = new Container();
        $container->set('service', 'value');

        $this->assertSame($container, $container->extend('service', function ($service) { return $service; }));
    }

    public function testExtensionQueueIsCalledOnlyOnce()
    {
        $container = new Container();

        $container->set('service', function () {
            return ['a', 'b'];
        });

        $container->extend(
            'service',
            function (array $foo) {
                $foo[] = 'c';

                return $foo;
            }
        );

        $this->assertEquals(['a', 'b', 'c'], $container->get('service'));
        $this->assertEquals($container->get('service'), $container->get('service'));
    }

    public function testMultipleExtend()
    {
        $container = new Container();

        $container->set(
            'service',
            function () {
                return new Service();
            }
        );

        $container->extend(
            'service',
            function (Service $service) {
                $service->concatenateParameter('a');
            }
        );

        $container->extend(
            'service',
            function (Service $service) {
                $service->concatenateParameter('b');
            }
        );

        $container->extend(
            'service',
            function (Service $service) {
                $service->concatenateParameter('c');
            }
        );

        $this->assertEquals('abc', $container->get('service')->getParameter());
    }

    public function testExtendWithAlias()
    {
        $container = new Container();

        $container->set(
            'original',
            function () {
                return new Service();
            }
        );

        $container->alias('alias', 'original');

        // extends original
        $container->extend(
            'original',
            function (Service $service) {
                $service->concatenateParameter('a');
            }
        );

        // extends alias
        $container->extend(
            'alias',
            function (Service $service) {
                $service->concatenateParameter('b');
            }
        );

        $this->assertEquals('ab', $container->get('original')->getParameter());
        $this->assertEquals('ab', $container->get('alias')->getParameter());
    }

    public function testExtendWithReturnValue()
    {
        $container = new Container();

        $container->set(
            'service',
            function () {
                return new Service();
            }
        );

        $container->set(
            'dependency',
            function () {
                return new Invokable();
            }
        );

        $container->extend(
            'service',
            function (Service $service, Container $container) {
                return new ServiceWithDependencies($service, $container->get('dependency'));
            }
        );

        $this->assertInstanceOf(ServiceWithDependencies::class, $container->get('service'));
    }

    public function testExtendWithArray()
    {
        $container = new Container();

        $container->set('service', [1, 2, 3]);
        $container->extend(
            'service',
            function ($original) {
                $original[] = 4;

                return $original;
            }
        );

        $this->assertEquals([1, 2, 3, 4], $container->get('service'));
    }

    public function testExtendValidatesKeyIsPresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The service "undefined" does not exist.');

        $container = new Container();
        $container->extend('undefined', function () {});
    }

    public function testFrozenContainer()
    {
        $container = new Container();
        $this->assertFalse($container->isFrozen());
        $container->freeze();
        $this->assertTrue($container->isFrozen());
    }

    /**
     * @covers \Lepre\DI\Exception\FrozenContainerException
     */
    public function testSetServiceOnFrozenContainer()
    {
        $this->expectException(FrozenContainerException::class);
        $this->expectExceptionMessage('The container is frozen and is not possible to define the new service "service-name".');

        $container = new Container();
        $container->freeze();

        $container->set('service-name', function () {});
    }

    public function testFreezeReturnsTheContainer()
    {
        $container = new Container();
        $this->assertSame($container, $container->freeze());
    }

    /**
     * @covers \Lepre\DI\Exception\FrozenContainerException
     */
    public function testExtendOnFrozenContainer()
    {
        $this->expectException(FrozenContainerException::class);
        $this->expectExceptionMessage('The container is frozen and is not possible to extend the service "service-name".');

        $container = new Container();
        $container->freeze();

        $container->extend('service-name', function () {});
    }

    public function testRegisterProvider()
    {
        $container = new Container();
        $this->assertFalse($container->has('service'));

        $container->register(new ServiceProvider());
        $this->assertTrue($container->has('service'));
        $this->assertInstanceOf(Service::class, $container->get('service'));
        $this->assertSame($container->get('service'), $container->get('service'));
    }

    public function testRegisterReturnsTheContainer()
    {
        $container = new Container();

        $this->assertSame($container, $container->register(new ServiceProvider()));
    }

    /**
     * @covers \Lepre\DI\Exception\FrozenContainerException
     */
    public function testRegisterProviderOnFrozenContainer()
    {
        $this->expectException(FrozenContainerException::class);
        $this->expectExceptionMessage('The container is frozen and is not possible to register the provider "Lepre\\DI\\Tests\\Fixtures\\ServiceProvider".');

        $container = new Container();
        $container->freeze();

        $container->register(new ServiceProvider());
    }
}
