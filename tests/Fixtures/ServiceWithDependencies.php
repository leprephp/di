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

final class ServiceWithDependencies
{
    public Service $foo;
    public Invokable $bar;

    public function __construct(Service $foo, Invokable $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
