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

namespace Rade\DI\Traits;

use Rade\DI\Extensions\ExtensionBuilder;
use Rade\DI\Extensions\ExtensionInterface;

/**
 * This trait adds container extensions builder to container.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
trait ExtensionTrait
{
    /**
     * Get the extension builder extensions.
     *
     * @return array<int,ExtensionInterface>
     */
    public function getExtensions(): array
    {
        $extensionBuilder = $this->getExtensionBuilder();

        return null !== $extensionBuilder ? $extensionBuilder->getExtensions() : [];
    }

    /**
     * Get the registered extension from builder.
     *
     * @param string $extensionName The extension class name or its alias
     */
    public function getExtension(string $extensionName): ?ExtensionInterface
    {
        $extensionBuilder = $this->getExtensionBuilder();

        return null !== $extensionBuilder ? $extensionBuilder->get($extensionName) : $extensionBuilder;
    }

    /**
     * Get the registered extension config from builder.
     *
     * @param string $extensionName The extension class name or its alias
     *
     * @return array<string,mixed>
     */
    public function getExtensionConfig(string $extensionName, string $parent = null): array
    {
        $extensionBuilder = $this->getExtensionBuilder();

        return null !== $extensionBuilder ? $extensionBuilder->getConfig($extensionName, $parent) : [];
    }

    /**
     * Checks if an extension is registered from builder.
     *
     * @param string $extensionName The extension class name or its alias
     */
    public function hasExtension(string $extensionName): bool
    {
        $extensionBuilder = $this->getExtensionBuilder();

        return null !== $extensionBuilder ? $extensionBuilder->has($extensionName) : false;
    }

    /**
     * Returns the container's extensions builder.
     */
    public function getExtensionBuilder(): ?ExtensionBuilder
    {
        return $this->definitions[ExtensionInterface::BUILDER] ?? null;
    }
}
