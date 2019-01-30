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

namespace Lepre\DI\Exception;

/**
 * This exception is thrown when you try to extend the container, but it is already frozen.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class FrozenContainerException extends \BadMethodCallException
{
}
