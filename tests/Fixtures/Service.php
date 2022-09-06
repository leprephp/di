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

final class Service
{
    protected string $parameter = '';

    public function setParameter(string $parameter)
    {
        $this->parameter = $parameter;
    }

    public function concatenateParameter(string $string)
    {
        $this->parameter .= $string;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }
}
