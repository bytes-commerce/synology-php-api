<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Resource;

final class Url
{
    public function __construct(
        private string $url,
        private string $method = 'GET',
        private array $parameters = []
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
