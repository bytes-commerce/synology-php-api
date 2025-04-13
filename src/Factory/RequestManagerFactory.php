<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Factory;

use BytesCommerce\SynologyApi\Manager\RequestManager;
use BytesCommerce\SynologyApi\Resource\EndpointProvider;
use SensitiveParameter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final readonly class RequestManagerFactory
{
    public function __construct(
        private ClientFactory $clientFactory,
        private EndpointProvider $endpointProvider,
        private EndpointDefinitionFactory $definitionFactory,
    ) {
    }

    public function createManager(
        string $targetUrl,
        string $userName,
        #[SensitiveParameter]
        string $password,
    ): RequestManager {
        return new RequestManager(
            $this->clientFactory->create(rtrim($targetUrl, '/')),
            $this->endpointProvider,
            $this->definitionFactory,
            new FilesystemAdapter(
                namespace: 'bc.synology_api',
                defaultLifetime: 0,
            ),
            $targetUrl,
            $userName,
            $password,
        );
    }
}
