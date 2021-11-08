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

use Psr\Container\ContainerInterface;
use Rade\DI\Exceptions\{ContainerResolutionException, NotFoundServiceException};

/**
 * A fully strict PSR-11 Container Implementation.
 *
 * This class is meant to be used as parent class for container's builder
 * compiled container class.
 *
 * Again, all services declared, should be autowired. Lazy services are not supported.
 *
 * @property-read array<string,string> $aliases
 * @property-read array<string,mixed> $services
 * @property-read array<string,mixed> $privates
 * @property-read array<string,string> $methodsMap
 * @property-read array<string,array<int,string>> $types
 * @property-read array<string,array<int,string>> $tags
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class SealedContainer implements ContainerInterface
{
    protected array $services = [], $privates = [], $methodsMap = [], $aliases = [], $types = [], $tags = [];

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return \array_key_exists($id = $this->aliases[$id] ?? $id, $this->methodsMap) || isset($this->types[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        if ($nullOnInvalid = '?' === $id[0]) {
            $id = \substr($id, 1);
        }

        if (\array_key_exists($id = $this->aliases[$id] ?? $id, $this->services)) {
            return $this->services[$id];
        }

        return AbstractContainer::SERVICE_CONTAINER === $id ? $this : ([$this, $this->methodsMap[$id] ?? 'doGet'])($id, $nullOnInvalid);
    }

    /**
     * Returns a set of service definition's belonging to a tag.
     *
     * @param string $tagName
     * @param string|null $serviceId If provided, tag value will return instead
     *
     * @return mixed
     */
    public function tagged(string $tagName, ?string $serviceId = null)
    {
        $tags = $this->tags[$tagName] ?? [];

        return null !== $serviceId ? $tags[$serviceId] ?? [] : $tags;
    }

    /**
     * Return the resolved service of an entry.
     *
     * @param bool|string $arrayLike
     *
     * @return mixed
     */
    protected function doGet(string $id, bool $nullOnInvalid)
    {
        if (\preg_match('/\[(.*)\]$/', $id, $matches, \PREG_UNMATCHED_AS_NULL)) {
            $autowired = $this->types[\str_replace($matches[0], '', $oldId = $id)] ?? [];

            if (!empty($autowired)) {
                if (isset($matches[1])) {
                    return $this->services[$oldId] = \array_map([$this, 'get'], $autowired);
                }

                foreach ($autowired as $serviceId) {
                    if ($serviceId === $matches[1]) {
                        return $this->get($this->aliases[$oldId] = $serviceId);
                    }
                }
            }
        } elseif (!empty($autowired = $this->types[$id] ?? [])) {
            if (\count($autowired) > 1) {
                \natsort($autowired);
                $autowired = count($autowired) <= 3 ? \implode(', ', $autowired) : $autowired[0] . ', ...' . \end($autowired);

                throw new ContainerResolutionException(\sprintf('Multiple services of type %s found: %s.', $id, $autowired));
            }

            if (!isset($this->aliases[$id])) {
                $this->aliases[$id] = $autowired[0];
            }

            return $this->services[$autowired[0]] ?? $this->get($autowired[0]);
        } elseif ($nullOnInvalid) {
            return null;
        }

        throw new NotFoundServiceException(\sprintf('The "%s" requested service is not defined in container.', $id));
    }

    private function __clone()
    {
    }
}