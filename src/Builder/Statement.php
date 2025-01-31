<?php

declare(strict_types=1);

/*
 * This file is part of DivineNii opensource projects.
 *
 * PHP version 7.4 and above required
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2021 DivineNii (https://divinenii.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rade\DI\Builder;

/**
 * Statement represents a service assigned call.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class Statement
{
    /** @var mixed */
    public $value;

    public array $args;

    /**
     * Statement constructor.
     *
     * @param mixed                   $value
     * @param array<int|string,mixed> $args
     */
    public function __construct($value, array $args = [])
    {
        $this->value = $value;
        $this->args = $args;
    }
}
