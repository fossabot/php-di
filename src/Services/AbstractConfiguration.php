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

namespace Rade\DI\Services;

use Psr\Container\ContainerInterface;

abstract class AbstractConfiguration implements ConfigurationInterface
{
    private array $config = [];

    /**
     * {@inheritdoc}
     */
    abstract public function setConfiguration(array $config, ContainerInterface $container): void;

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        if ([] === $this->config) {
            throw new \RuntimeException(
                'Configurations for this provider is empty. See \'setConfiguration\' method.'
            );
        }

        return $this->config;
    }
}
