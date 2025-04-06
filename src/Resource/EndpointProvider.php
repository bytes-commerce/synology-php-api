<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Resource;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Webmozart\Assert\Assert;

final class EndpointProvider
{
    private FilesystemAdapter $adapter;

    public function __construct(
    ) {
        $this->adapter = new FilesystemAdapter();
    }

    public function getEndpoints(string $targetUrl): array
    {
        return $this->adapter->get('bc.synology_api.endpoints', function (ItemInterface $item) use ($targetUrl): array {
            $item->expiresAfter(7200);

            $ch = curl_init();
            $url = sprintf('%s/webapi/query.cgi?api=SYNO.API.Info&version=1&method=query', $targetUrl);
            curl_setopt($ch, \CURLOPT_URL, $url);

            $referer = $this->getReferer($targetUrl);

            curl_setopt($ch, \CURLOPT_REFERER, $referer);
            curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            Assert::same(0, curl_errno($ch));
            curl_close($ch);
            Assert::string($response);

            return json_decode($response, true);
        });
    }

    public function getReferer(string $targetUrl): string
    {
        $urlParts = parse_url($targetUrl);
        Assert::isArray($urlParts);
        Assert::keyExists($urlParts, 'host');
        Assert::keyExists($urlParts, 'scheme');

        $hostParts = explode('.', $urlParts['host']);
        if (count($hostParts) === 4) {
            unset($hostParts[1]);
        }

        return sprintf('%s://%s', $urlParts['scheme'], implode('.', $hostParts));
    }
}
