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

use Lepre\DI\Exception\FrozenContainerException;
use Psr\Container\ContainerInterface;

/**
 * Container.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $definitions = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @var ExtensionQueue[]
     */
    private $extensionQueues = [];

    /**
     * @var bool
     */
    private $frozen = false;

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        $id = $this->getRealId($id);

        if (!array_key_exists($id, $this->services)) {
            $this->services[$id] = $this->getNew($id);
        }

        return $this->services[$id];
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return array_key_exists($this->getRealId($id), $this->definitions);
    }

    /**
     * Defines a new service.
     *
     * @param string $id
     * @param mixed  $service
     * @return $this
     */
    public function set(string $id, $service)
    {
        if ($this->frozen) {
            throw new FrozenContainerException(
                sprintf('The container is frozen and is not possible to define the new service "%s".', $id)
            );
        }

        $this->definitions[$id] = $service;

        // clean alias and internal cache
        unset($this->services[$id]);
        unset($this->aliases[$id]);

        return $this;
    }

    /**
     * Sets an alias for a service.
     *
     * @param string $alias
     * @param string $original
     * @return $this
     */
    public function alias(string $alias, string $original)
    {
        $this->aliases[$alias] = $this->getRealId($original);

        return $this;
    }

    /**
     * Forces the container to return a new instance of the service.
     *
     * @param string $id
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getNew(string $id)
    {
        $definition = $this->raw($id);

        if (is_callable($definition)) {
            $service = call_user_func($definition, $this);
        } else {
            $service = $definition;
        }

        if (isset($this->extensionQueues[$id])) {
            $service = $this->extensionQueues[$id]->getService($service);
        }

        return $service;
    }

    /**
     * Gets the raw definition of the service.
     *
     * @param string $id
     * @return mixed
     * @throws Exception\NotFoundException
     */
    public function raw(string $id)
    {
        $id = $this->getRealId($id);

        if (array_key_exists($id, $this->definitions)) {
            return $this->definitions[$id];
        }

        throw new Exception\NotFoundException($id);
    }

    /**
     * Extends a service definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string   $id
     * @param callable $callable
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function extend(string $id, callable $callable)
    {
        if ($this->frozen) {
            throw new FrozenContainerException(
                sprintf('The container is frozen and is not possible to extend the service "%s".', $id)
            );
        }

        $id = $this->getRealId($id);

        if (!array_key_exists($id, $this->definitions)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" does not exist.', $id));
        }

        if (!isset($this->extensionQueues[$id])) {
            $this->extensionQueues[$id] = new ExtensionQueue($this);
        }

        $this->extensionQueues[$id]->add($callable);

        return $this;
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider
     * @return $this
     */
    public function register(ServiceProviderInterface $provider)
    {
        if ($this->frozen) {
            throw new FrozenContainerException(
                sprintf(
                    'The container is frozen and is not possible to register the provider "%s".',
                    get_class($provider)
                )
            );
        }

        $provider->register($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function freeze()
    {
        $this->frozen = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * @param string $id
     * @return string
     */
    private function getRealId(string $id): string
    {
        if (array_key_exists($id, $this->aliases)) {
            return $this->aliases[$id];
        }

        return $id;
    }
}
