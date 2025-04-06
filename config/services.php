<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BytesCommence\SynologyApi\Factory\ApiActionItemFactory;
use BytesCommerce\SynologyApi\Factory\ClientFactory;
use BytesCommerce\SynologyApi\Factory\EndpointDefinitionFactory;
use BytesCommerce\SynologyApi\Factory\RequestManagerFactory;
use BytesCommerce\SynologyApi\Items\ActionItemInterface;
use BytesCommerce\SynologyApi\Items\CreateItem;
use BytesCommerce\SynologyApi\Items\GetInfoItem;
use BytesCommerce\SynologyApi\Items\GetItem;
use BytesCommerce\SynologyApi\Items\ListItem;
use BytesCommerce\SynologyApi\Items\ListShareItem;
use BytesCommerce\SynologyApi\Items\LoginItem;
use BytesCommerce\SynologyApi\Items\LogoutItem;
use BytesCommerce\SynologyApi\Items\QueryItem;
use BytesCommerce\SynologyApi\Items\StartItem;
use BytesCommerce\SynologyApi\Items\StatusItem;
use BytesCommerce\SynologyApi\Items\StopItem;
use BytesCommerce\SynologyApi\Items\TokenItem;
use BytesCommerce\SynologyApi\Items\UploadItem;
use BytesCommerce\SynologyApi\Resource\EndpointProvider;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->private()
            ->instanceof(ActionItemInterface::class)
            ->tag(ActionItemInterface::ITEM_TAG);

    $services->set(CreateItem::class);
    $services->set(GetInfoItem::class);
    $services->set(GetItem::class);
    $services->set(ListItem::class);
    $services->set(ListShareItem::class);
    $services->set(LoginItem::class);
    $services->set(LogoutItem::class);
    $services->set(QueryItem::class);
    $services->set(TokenItem::class);
    $services->set(UploadItem::class);
    $services->set(StartItem::class);
    $services->set(StatusItem::class);
    $services->set(StopItem::class);

    $services->set(ApiActionItemFactory::class);
    $services->set(EndpointProvider::class);
    $services->set(EndpointDefinitionFactory::class)
        ->arg('$actionItems', tagged_iterator(ActionItemInterface::ITEM_TAG))
        ->arg('$apiActionItemFactory', service(ApiActionItemFactory::class));

    $services->set(ClientFactory::class)
        ->arg('$httpClient', service('http_client'))
        ->arg('$endpointProvider', service(EndpointProvider::class));

    $services->public()
        ->set(RequestManagerFactory::class)
        ->arg('$clientFactory', service(ClientFactory::class))
        ->arg('$endpointProvider', service(EndpointProvider::class))
        ->arg('$definitionFactory', service(EndpointDefinitionFactory::class))
    ;
};
