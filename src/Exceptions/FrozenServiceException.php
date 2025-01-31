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

namespace Rade\DI\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * An attempt to modify a frozen service was made.
 */
class FrozenServiceException extends \RuntimeException implements ContainerExceptionInterface
{
    /**
     * @param string $id Identifier of the frozen service
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Cannot override frozen service "%s".', $id));
    }
}
