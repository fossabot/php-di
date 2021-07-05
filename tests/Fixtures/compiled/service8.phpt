<?php

declare (strict_types=1);

/**
 * @internal This class has been auto-generated by the Rade DI.
 */
class DeprecatedContainer extends Rade\DI\Container
{
    public array $parameters = [];

    protected static array $privates = [];

    protected array $methodsMap = ['deprecate_service' => 'getDeprecateService', 'container' => 'getServiceContainer'];

    protected array $types = [Rade\DI\AbstractContainer::class => ['container'], Psr\Container\ContainerInterface::class => ['container'], Rade\DI\Container::class => ['container']];

    protected array $aliases = [];

    protected function getDeprecateService(): Rade\DI\Tests\Fixtures\Service
    {
        \trigger_deprecation('', '', 'The "%s" service is deprecated. You should stop using it, as it will be removed in the future.', 'deprecate_service');

        return self::$services['deprecate_service'] = new Rade\DI\Tests\Fixtures\Service();
    }
}