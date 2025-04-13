<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

use Webmozart\Assert\Assert;

abstract class AbstractActionItem implements \JsonSerializable, ActionItemInterface
{
    public const IS_MULTIPART_FORM = false;

    public function __construct(
        private ?string $api = null,
        private ?string $path = null,
        private ?int $version = null,
        private bool $jsonRequest = false,
    ) {
    }

    abstract public function getAvailableKeys(): array;

    abstract public function getScope(): array;

    public function isMultipartForm(): bool
    {
        return static::IS_MULTIPART_FORM;
    }

    public function isDownloadAction(): bool
    {
        return false;
    }

    public function parameterOrderValidator(array $params): bool
    {
        return true;
    }

    public function isJsonRequest(): bool
    {
        Assert::notNull($this->jsonRequest, 'The jsonRequest property must be set before calling isJsonRequest()');

        return $this->jsonRequest;
    }

    public function getApi(): string
    {
        Assert::notNull($this->api, 'The api property must be set before calling getApi()');

        return $this->api;
    }

    public function getVersion(): int
    {
        Assert::notNull($this->version, 'The version property must be set before calling getVersion()');

        return $this->version;
    }

    public function getMethod(): string
    {
        /** @phpstan-ignore-next-line */
        return static::METHOD;
    }

    public function getPath(): string
    {
        Assert::notNull($this->path, 'The path property must be set before calling getPath()');

        return $this->path;
    }

    public function jsonSerialize(): string
    {
        /** @phpstan-ignore-next-line */
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'api' => $this->getApi(),
            'version' => $this->getVersion(),
            'method' => $this->getMethod(),
            'path' => $this->getPath(),
        ];
    }
}
