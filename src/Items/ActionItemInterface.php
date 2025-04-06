<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

interface ActionItemInterface
{
    public const ITEM_TAG = 'bc.synology_api.action_item';

    public function getAvailableKeys(): array;

    public function getScope(): array;

    public function parameterOrderValidator(array $params): bool;

    public function isJsonRequest(): bool;

    public function getApi(): string;

    public function getVersion(): int;

    public function getMethod(): string;

    public function getPath(): string;

    public function toArray(): array;
}
