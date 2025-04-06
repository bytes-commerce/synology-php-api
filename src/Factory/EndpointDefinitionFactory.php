<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Factory;

use BytesCommence\SynologyApi\Factory\ApiActionItemFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Webmozart\Assert\Assert;

final class EndpointDefinitionFactory
{
    public function __construct(
        private iterable $actionItems,
        private ApiActionItemFactory $apiActionItemFactory,
    ) {
        Assert::isInstanceOf($this->actionItems, RewindableGenerator::class);
        Assert::notSame(0, $this->actionItems->count());
    }

    public function createAll(array $inputInformation): array
    {
        $scopeCandidates = [];
        foreach (iterator_to_array($this->actionItems) as $actionItem) {
            $scopes = $actionItem->getScope();
            foreach ($scopes as $scope) {
                $scopeCandidates[$scope][] = $actionItem;
            }
        }

        $definitions = [];
        foreach ($scopeCandidates as $scope => $actionItems) {
            if (array_key_exists($scope, $inputInformation)) {
                foreach ($actionItems as $actionItem) {
                    $payload[$actionItem::class] = $this->apiActionItemFactory->createConcrete($actionItem::class, $scope, $inputInformation[$scope]);
                }

                $definitions[$scope] = new ArrayCollection($payload);
            }
        }

        return $definitions;
    }
}
