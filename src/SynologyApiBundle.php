<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi;

use BytesCommerce\SynologyApi\Items\ActionItemInterface;
use ReflectionObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Webmozart\Assert\Assert;
use function dirname;

/**
 * @author Maximilian Graf Schimmelmann <schimmelmann@bytes-commerce.de>
 */
class SynologyApiBundle extends Bundle
{
    public function getPath(): string
    {
        $reflected = new ReflectionObject($this);
        $name = $reflected->getFileName();
        Assert::string($name);

        return dirname($name, 2);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(ActionItemInterface::class)
            ->addTag('bc.synology_api.action_item')
        ;
    }
}
