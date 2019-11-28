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
 * Service Provider Interface.
 *
 * This class is loosely based on the Pimple project {@link https://github.com/silexphp/Pimple}
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A Container instance
     */
    public function register(Container $container): void;
}
