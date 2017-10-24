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

namespace Lepre\Component\DI\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * NotFoundException
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string          $id
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($id, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('Service "%s" not exists.', $id), $code, $previous);
    }
}