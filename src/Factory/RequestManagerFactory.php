<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Factory;

use BytesCommerce\SynologyApi\Manager\RequestManager;
use BytesCommerce\SynologyApi\Resource\EndpointProvider;
use SensitiveParameter;

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
            $this->clientFactory->create($targetUrl),
            $this->endpointProvider,
            $this->definitionFactory,
            $targetUrl,
            $userName,
            $password,
        );
    }
}
