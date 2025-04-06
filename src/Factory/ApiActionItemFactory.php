<?php

declare(strict_types=1);

namespace BytesCommence\SynologyApi\Factory;

use BytesCommerce\SynologyApi\Items\AbstractActionItem;

final class ApiActionItemFactory
{
    public function createConcrete(string $className, string $apiName, array $information): AbstractActionItem
    {
        if (!class_exists($className) || !is_subclass_of($className, AbstractActionItem::class)) {
            throw new \RuntimeException(sprintf('Class %s does not exist', $className));
        }

        return new $className(
            $apiName,
            $information['path'],
            max($information['maxVersion'] ?? 0, $information['minVersion'] ?? 0),
            ($information['requestFormat'] ?? null) === 'JSON',
        );
    }
}
