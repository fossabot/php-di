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

namespace Rade\DI\Attribute;

use Rade\DI\Resolver;

/**
 * Marks a property or method as an injection point.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_PARAMETER)]
final class Inject
{
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Resolve the value of the injection point.
     *
     * @return mixed
     */
    public function resolve(Resolver $resolver, string $typeName = null)
    {
        if (\is_string($value = $this->value ?? $typeName)) {
            return $resolver->resolveReference($value);
        }

        if (null !== $value) {
            return $resolver->resolve($value);
        }

        return $value;
    }
}
