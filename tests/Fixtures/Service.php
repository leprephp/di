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

class Service
{
    protected $parameter;

    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }

    public function concatenateParameter($string)
    {
        $this->parameter .= $string;
    }

    public function getParameter()
    {
        return $this->parameter;
    }
}
