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

namespace Lepre\DI\Tests\Fixtures;

use Lepre\DI\Container;
use Lepre\DI\ServiceProviderInterface;

final class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set('service', function () {
            return new Service();
        });
    }
}
