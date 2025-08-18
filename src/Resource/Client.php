<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Resource;

use BytesCommerce\SynologyApi\Enum\ErrorCodeEnum;
use BytesCommerce\SynologyApi\Enum\FileStationErrorCodeEnum;
use BytesCommerce\SynologyApi\Exceptions\UserAccessException;
use BytesCommerce\SynologyApi\Items\AbstractActionItem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Client
{
    private ?string $sid = null;

    private ?string $synoToken = null;

    public function __construct(
        private readonly string $targetUrl,
        private readonly string $referer,
        private readonly HttpClientInterface $client,
    ) {
    }

    public function __destruct()
    {
        if ($this->sid !== null) {
            $this->resetTokens();
        }
    }

    public function request(AbstractActionItem $actionItem, array $parameters = []): ArrayCollection
    {
        $url = $this->getUrl($actionItem, $parameters);
        $response = $this->client->request(strtoupper($url->getMethod()), $url->getUrl(), $url->getParameters());
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf('Request failed with HTTP status code %d', $response->getStatusCode()));
        }

        $data = $actionItem->isDownloadAction()
            ? ['file' => $response->getContent()]
            : json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (isset($data['success']) && !$data['success']) {
            $errorCode = (int) $data['error']['code'];

            if ($errorCode === 119) {
                throw new UserAccessException();
            }

            throw new \RuntimeException(
                sprintf(
                    "[%s] [%s] [%s] [%s] failed with error %s %s\n\n%s",
                    $url->getMethod(),
                    $actionItem->getApi(),
                    $actionItem->getMethod(),
                    $actionItem->getVersion(),
                    $errorCode,
                    $this->getErrorLabel($errorCode, $actionItem),
                    $url->getUrl(),
                ),
            );
        }

        if (!isset($data['data'])) {
            return new ArrayCollection($data);
        }

        return new ArrayCollection($data['data']);
    }

    private function generateBaseUrl(AbstractActionItem $actionItem): string
    {
        return sprintf(
            '%s/webapi/%s',
            $this->targetUrl,
            $actionItem->getPath(),
        );
    }

    private function generateUrl(AbstractActionItem $actionItem, array $parameters): string
    {
        $this->checkParameters($actionItem, $parameters);

        $result = sprintf(
            '%s?%s',
            $this->generateBaseUrl($actionItem),
            http_build_query($this->getQueryParameters($actionItem, $parameters)),
        );

        return str_replace('%5C', '', $result);
    }

    private function recursiveImplode(string $separator, array $array): string
    {
        $flattened = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                $flattened[] = $this->recursiveImplode($separator, $item);
            } else {
                $flattened[] = $item;
            }
        }

        return implode($separator, $flattened);
    }

    public function getSynologyToken(): ?string
    {
        return $this->synoToken;
    }

    public function setSynologyToken(?string $synologyToken): void
    {
        $this->synoToken = $synologyToken;
    }

    public function getSessionId(): ?string
    {
        return $this->sid;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sid = $sessionId;
    }

    private function getErrorLabel(int $errorCode, AbstractActionItem $actionItem): string
    {
        $fileStation = str_contains($actionItem->getApi(), 'FileStation');
        if ($fileStation && FileStationErrorCodeEnum::has($errorCode)) {
            return FileStationErrorCodeEnum::from($errorCode)->getLabel();
        }

        if (ErrorCodeEnum::has($errorCode)) {
            return ErrorCodeEnum::from($errorCode)->getLabel();
        }

        return sprintf('JSON Error: %s', json_last_error());
    }

    private function checkParameters(AbstractActionItem $actionItem, array $parameters): void
    {
        $availableKeys = $actionItem->getAvailableKeys();
        foreach (array_keys($parameters) as $key) {
            if (!in_array($key, $availableKeys, true) && (array_key_exists('optional', $availableKeys) && !in_array(
                        $key,
                        $availableKeys['optional'],
                        true,
                    ))) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid parameter "%s" for action item "%s", required keys are %s',
                        $key,
                        $actionItem->getPath(),
                        $this->recursiveImplode(', ', $availableKeys),
                    ),
                );
            }
        }
    }

    private function getQueryParameters(AbstractActionItem $actionItem, array $parameters): array
    {
        $queryParameters = [
            'api' => $actionItem->getApi(),
            'version' => $actionItem->getVersion(),
            'method' => $actionItem->getMethod(),
            ...$parameters,
        ];

        if ($this->getSynologyToken() !== null) {
            $queryParameters['SynoToken'] = $this->getSynologyToken();
        }

        return array_filter($queryParameters);
    }

    public function hasTokens(): bool
    {
        return $this->getSynologyToken() !== null && $this->getSessionId() !== null;
    }

    private function resetTokens(): void
    {
        $this->setSessionId(null);
        $this->setSynologyToken(null);
    }

    public function getUrl(AbstractActionItem $actionItem, array $parameters): Url
    {
        $url = $this->generateUrl($actionItem, $parameters);

        $method = Request::METHOD_GET;
        $requestParameter = array_filter([
            'headers' => [
                'Referer' => $this->referer,
                'Cookie' => sprintf('id=%s', $this->getSessionId()),
            ],
            'timeout' => 10,
            'max_duration' => 10,
        ]);

        if ($actionItem->isMultipartForm()) {
            $method = Request::METHOD_POST;
            $url = $this->generateBaseUrl($actionItem);
            $url .= sprintf('?SynoToken=%s', $this->getSynologyToken());

            $parameters['api'] = $actionItem->getApi();
            $parameters['version'] = (string)$actionItem->getVersion();
            $parameters['method'] = $actionItem->getMethod();

            if (array_key_exists('filename', $parameters) && file_exists($parameters['filename'])) {
                $parameters['filename'] = fopen($parameters['filename'], 'r');
            }

            $requestParameter = array_merge_recursive($requestParameter, [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => array_reverse($parameters),
            ]);
        }

       return new Url($url, $method, $requestParameter);
    }
}
