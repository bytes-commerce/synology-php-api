<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Factory;

use BytesCommerce\SynologyApi\Resource\Client;
use BytesCommerce\SynologyApi\Resource\EndpointProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ClientFactory
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EndpointProvider $endpointProvider,
    ) {
    }

    public function create(string $targetUrl): Client
    {
        return new Client(
            $targetUrl,
            $this->endpointProvider->getReferer($targetUrl),
            $this->httpClient,
        );
    }
}
