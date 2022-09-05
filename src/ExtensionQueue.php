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

namespace Lepre\DI;

/**
 * The extension queue allows you to extend a service.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class ExtensionQueue
{
    /**
     * @var array
     */
    private array $queue = [];

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add an extension.
     *
     * @param callable $callable
     * @return $this
     */
    public function add(callable $callable): self
    {
        $this->queue[] = $callable;

        return $this;
    }

    /**
     * Gets the extended version of the service.
     *
     * @param mixed $service
     * @return mixed
     */
    public function getService($service)
    {
        foreach ($this->queue as $extension) {
            $new = call_user_func($extension, $service, $this->container);

            if ($new) {
                $service = $new;
            }
        }

        return $service;
    }
}
