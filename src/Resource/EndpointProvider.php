<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Resource;

use BytesCommerce\SynologyApi\Exceptions\NoConnectionException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Webmozart\Assert\Assert;

final class EndpointProvider
{
    private FilesystemAdapter $adapter;

    public function __construct(
    ) {
        $this->adapter = new FilesystemAdapter(
            namespace: 'bc.synology_api',
            defaultLifetime: 0,
        );
    }

    public function getEndpoints(string $targetUrl): array
    {
        return $this->adapter->get('endpoints', function (ItemInterface $item) use ($targetUrl): array {
            $item->expiresAfter(7200);

            $result = [];

            $i = 0;
            while (true) {
                if (++$i > 10) {
                    throw new \RuntimeException('Unable to get endpoints from Synology API');
                }

                $ch = curl_init();
                $url = sprintf('%s/webapi/query.cgi?api=SYNO.API.Info&version=1&method=query', $targetUrl);
                curl_setopt($ch, \CURLOPT_URL, $url);

                $referer = $this->getReferer($targetUrl);

                curl_setopt($ch, \CURLOPT_REFERER, $referer);
                curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, \CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 3);

                $response = curl_exec($ch);
                Assert::same(0, curl_errno($ch));
                curl_close($ch);
                try {
                    Assert::string($response);
                } catch (Throwable $e) {
                    throw new NoConnectionException();
                }

                $result = json_decode($response, true);
                if ($result !== null) {
                    break;
                }
            }

            return $result;
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
