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

namespace Rade\DI;

use Nette\Utils\Validators;
use Rade\DI\Definitions\DefinitionInterface;
use Rade\DI\Definitions\ShareableDefinitionInterface;
use Rade\DI\Exceptions\{CircularReferenceException, ContainerResolutionException, FrozenServiceException, NotFoundServiceException};

/**
 * Dependency injection container.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class Container extends AbstractContainer implements \ArrayAccess
{
    /** @var array<string,string> internal cached services */
    protected array $methodsMap = [];

    /**
     * Sets a new service to a unique identifier.
     *
     * @param string $offset The unique identifier for the parameter or object
     * @param mixed  $value  The value of the service assign to the $offset
     *
     * @throws FrozenServiceException Prevent override of a frozen service
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->autowire($offset, $value);
    }

    /**
     * Gets a registered service definition.
     *
     * @param string $offset The unique identifier for the service
     *
     * @throws NotFoundServiceException If the identifier is not defined
     *
     * @return mixed The value of the service
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Checks if a service is set.
     *
     * @param string $offset The unique identifier for the service
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Unset a service by given offset.
     *
     * @param string $offset The unique identifier for service definition
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        $this->removeDefinition($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @throws FrozenServiceException if definition has been initialized
     */
    public function definition(string $id)
    {
        if ('?' === $id[0]) {
            $id = \substr($id, 1);
        } elseif (\array_key_exists($id, $this->services)) {
            throw new FrozenServiceException(\sprintf('The "%s" service is already initialized.', $id));
        }

        return parent::definition($id);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $id, callable $scope = null)
    {
        if (isset($this->methodsMap[$id])) {
            throw new FrozenServiceException(\sprintf('The internal service definition for "%s", cannot be overwritten.', $id));
        }

        return parent::extend($id, $scope);
    }

    /**
     * {@inheritdoc}
     */
    public function keys(): array
    {
        return parent::keys() + \array_keys($this->methodsMap);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id, int $invalidBehavior = /* self::EXCEPTION_ON_MULTIPLE_SERVICE */ 1)
    {
        if (\array_key_exists($id = $this->aliases[$id] ?? $id, $this->services)) {
            return $this->services[$id];
        }

        return self::SERVICE_CONTAINER === $id ? $this : ([$this, $this->methodsMap[$id] ?? 'doGet'])($id, $invalidBehavior);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return parent::has($id) || \array_key_exists($this->aliases[$id] ?? $id, $this->methodsMap);
    }

    /**
     * {@inheritdoc}
     */
    protected function createDefinition(string $id, $definition)
    {
        $definition = parent::createDefinition($id, $definition);

        if ($definition instanceof DefinitionInterface || \is_callable($definition) || \is_object($definition)) {
            return $definition;
        }

        if (\is_string($definition) && Validators::isType($definition)) {
            return $this->createDefinition($id, new Definition($definition));
        }

        return fn () => $this->resolver->resolve($definition);
    }

    /**
     * Build an entry of the container by its name.
     *
     * @throws CircularReferenceException|NotFoundServiceException
     *
     * @return mixed
     */
    protected function doGet(string $id, int $invalidBehavior)
    {
        $createdService = parent::doGet($id, $invalidBehavior);

        if (null === $createdService) {
            try {
                $anotherService = $this->resolver->resolve($id);

                if ($id !== $anotherService) {
                    return self::IGNORE_SERVICE_INITIALIZING === $invalidBehavior ? $anotherService : $this->services[$id] = $anotherService;
                }
            } catch (ContainerResolutionException $e) {
                // Skip error throwing while resolving
            }

            if (self::NULL_ON_INVALID_SERVICE !== $invalidBehavior) {
                throw $this->createNotFound($id);
            }
        }

        return $createdService;
    }

    /**
     * @param DefinitionInterface|callable|mixed
     */
    protected function doCreate(string $id, $definition, int $invalidBehavior)
    {
        if ($definition instanceof DefinitionInterface) {
            if ($definition instanceof ShareableDefinitionInterface) {
                if (!$definition->isPublic()) {
                    $this->removeDefinition($id); // Definition service available once, else if shareable, accessed from cache.
                }

                if (!$definition->isShared()) {
                    return $definition->build($id, $this->resolver);
                }
            }

            $definition = $definition->build($id, $this->resolver);
        } elseif (\is_callable($definition)) {
            $definition = $this->resolver->resolve($definition);
        }

        if (self::IGNORE_SERVICE_FREEZING === $invalidBehavior) {
            return $this->services[$id] = $definition;
        }

        if (self::IGNORE_SERVICE_INITIALIZING === $invalidBehavior) {
            return $this->definitions[$id] = $definition;
        }

        return $this->definitions[$id] = $this->services[$id] = $definition;
    }
}
