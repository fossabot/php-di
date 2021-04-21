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

namespace Rade\DI\Facade;

use PhpParser\Node\{
    Expr\StaticPropertyFetch,
    Name,
    Stmt\Declare_,
    Stmt\DeclareDeclare,
    Stmt\Return_,
    UnionType
};
use Psr\Container\ContainerInterface;
use Rade\DI\Builder\CodePrinter;
use Rade\DI\{ContainerBuilder, Definition};

/**
 * A Proxy manager for implementing laravel like facade system.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class FacadeProxy
{
    private ContainerInterface $container;

    private array $proxies = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register(s) service{s) found in container as shared proxy facade(s).
     */
    public function proxy(string ...$services): void
    {
        foreach ($services as $service) {
            $id = \str_replace(['.', '_'], '', \lcfirst(\ucwords($service, '._')));

            if (!$this->container instanceof ContainerBuilder) {
                Facade::$proxies[$id] = $service;

                continue;
            }

            $this->proxies[] = $service;
        }
    }

    /**
     * This build method works with container builder.
     *
     * @param string $className for compiled facade class
     */
    public function build(string $className = 'Facade'): ?string
    {
        /** @var ContainerBuilder */
        $container = $this->container;

        if ([] !== $this->proxies) {
            $astNodes = [];
            $builder = $container->getBuilder();

            $astNodes[] = new Declare_([new DeclareDeclare('strict_types', $builder->val(1))]);
            $classNode = $builder->class($className)->extend('\Rade\DI\Facade\Facade')->setDocComment(CodePrinter::COMMENT);

            foreach ($this->proxies as $method => $proxy) {
                if ($container->has($proxy)) {
                    $definition = $container->extend($proxy);

                    if ($definition->is(Definition::PRIVATE)) {
                        continue;
                    }

                    $proxyNode = $builder->method($method)->makePublic()->makeStatic();
                    ($property = new \ReflectionProperty($definition, 'type'))->setAccessible(true);

                    if (!empty($type = $property->getValue($definition))) {
                        $proxyNode->setReturnType(\is_array($type) ? new UnionType($type) : $type);
                    }

                    $body = $builder->methodCall(new StaticPropertyFetch(new Name('self'), 'container'), 'get', [$proxy]);
                    $classNode->addStmt($proxyNode->addStmt(new Return_($body)));
                }
            }
            $astNodes[] = $classNode->getNode();

            return CodePrinter::print($astNodes);
        }

        return null;
    }
}
