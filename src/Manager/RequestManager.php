<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Manager;

use BytesCommerce\SynologyApi\Exceptions\NoConnectionException;
use BytesCommerce\SynologyApi\Exceptions\UserAccessException;
use BytesCommerce\SynologyApi\Factory\EndpointDefinitionFactory;
use BytesCommerce\SynologyApi\Items\AbstractActionItem;
use BytesCommerce\SynologyApi\Items\CreateItem;
use BytesCommerce\SynologyApi\Items\ListItem;
use BytesCommerce\SynologyApi\Items\ListShareItem;
use BytesCommerce\SynologyApi\Items\LoginItem;
use BytesCommerce\SynologyApi\Items\QueryItem;
use BytesCommerce\SynologyApi\Items\StartItem;
use BytesCommerce\SynologyApi\Items\StatusItem;
use BytesCommerce\SynologyApi\Items\UploadItem;
use BytesCommerce\SynologyApi\Resource\Client;
use BytesCommerce\SynologyApi\Resource\EndpointProvider;
use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;
use SensitiveParameter;
use Throwable;
use Webmozart\Assert\Assert;

final class RequestManager
{
    private array $webApiInformation = [];

    public function __construct(
        private readonly Client $client,
        private readonly EndpointProvider $endpointProvider,
        private readonly EndpointDefinitionFactory $definitionFactory,
        private readonly string $targetUrl,
        private readonly string $username,
        #[SensitiveParameter]
        private readonly string $password,
    ) {
    }

    public function mkdir(array $sharePaths, array $filePaths, bool $recursive): ArrayCollection
    {
        Assert::same(count($sharePaths), count($filePaths), sprintf('The number of 1st parameter $sharePaths (%d) must be equal to the number 2nd parameter $filePaths (%d).', count($sharePaths), count($filePaths)));
        $webApi = $this->getApiActionItem('SYNO.FileStation.CreateFolder', CreateItem::class);

        return $this->request($webApi, [
            'folder_path' => json_encode($sharePaths),
            'name' => json_encode($filePaths),
            'force_parent' => $recursive,
            'additional' => json_encode([
                'real_path',
                'size',
                'type',
            ]),
        ]);
    }

    public function ls(string $path): ArrayCollection
    {
        $webApi = $this->getApiActionItem('SYNO.FileStation.List', ListItem::class);

        return $this->request($webApi, [
            'folder_path' => sprintf('"%s"', $path),
            'additional' => json_encode([
                'real_path',
                'size',
                'type',
            ]),
        ]);
    }

    public function getShares(): ArrayCollection
    {
        $webApi = $this->getApiActionItem('SYNO.FileStation.List', ListShareItem::class);

        return $this->request($webApi, [
            'additional' => json_encode([
                'real_path',
                'size',
                'type',
                'volume_status',
            ]),
        ]);
    }

    public function upload(string $fileTargetPath, string $filePath): ArrayCollection
    {
        $webApi = $this->getApiActionItem('SYNO.FileStation.Upload', UploadItem::class);

        return $this->request($webApi, array_reverse([
            'path' => $fileTargetPath,
            'create_parents' => 'true',
            'overwrite' => 'overwrite',
            'additional' => json_encode([
                'real_path',
                'size',
                'type',
            ]),
            'filename' => $filePath,
        ]));
    }

    public function delete(array $paths, bool $recursive = false): ArrayCollection
    {
        Assert::allString($paths);
        Assert::allNotEmpty($paths);
        $webApi = $this->getApiActionItem('SYNO.FileStation.Delete', StartItem::class);

        $startResponse = $this->request($webApi, [
            'path' => json_encode($paths),
            'recursive' => $recursive,
        ]);

        $response = ['finished' => false];
        $counter = 0;
        $sleepDuration = 0;
        while ($response['finished'] === false) {
            if ($counter > 100) {
                throw new RuntimeException('Timeout while waiting for delete task to finish.');
            }

            usleep($sleepDuration);
            $webApi = $this->getApiActionItem('SYNO.FileStation.Delete', StatusItem::class);
            $response = $this->request($webApi, [
                'taskid' => $startResponse->get('taskid'),
            ]);
            $sleepDuration += 1000;
            ++$counter;
        }

        return $response;
    }

    public function request(AbstractActionItem $actionItem, array $parameters = [], bool $reLogin = false): ArrayCollection
    {
        if (!$this->canConnect()) {
            throw new NoConnectionException();
        }

        if (!$this->client->hasTokens() || $reLogin) {
            $this->login();
        }

        try {
            $actionItem->parameterOrderValidator($parameters);

            return $this->client->request($actionItem, $parameters);
        } catch (UserAccessException $e) {
            if ($reLogin) {
                throw $e;
            }

            $this->resetTokens();

            return $this->request($actionItem, $parameters, true);
        }
    }

    private function canConnect(): bool
    {
        try {
            return $this->getWebApiInformation() !== [];
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getApiActionItem(
        string $apiName,
        string $className = QueryItem::class,
    ): AbstractActionItem {
        $allKeys = $this->getWebApiInformation();
        Assert::keyExists($allKeys, $apiName);

        $apiInformation = $allKeys[$apiName];
        if ($apiInformation->count() === 0) {
            throw new RuntimeException(sprintf('API with key "%s" has no elements, check the definitions.', $apiName));
        }

        if ($apiInformation->get($className) === null) {
            throw new RuntimeException(sprintf('Method "%s" not found in API with key "%s", available options: %s.', $className, $apiName, implode(', ', $apiInformation->getKeys())));
        }

        return $apiInformation->get($className);
    }

    /**
     * @return ArrayCollection[]
     */
    public function getWebApiInformation(): array
    {
        if ($this->webApiInformation === []) {
            $result = $this->endpointProvider->getEndpoints($this->targetUrl);
            if ($result['data'] === null) {
                throw new RuntimeException('No data found in the response.');
            }

            $this->webApiInformation = $this->definitionFactory->createAll($result['data']);
        }

        return $this->webApiInformation;
    }

    private function login(): void
    {
        $loginItem = $this->getApiActionItem('SYNO.API.Auth', LoginItem::class);
        $result = $this->client->request($loginItem, array_filter([
            'account' => $this->username,
            'passwd' => $this->password,
            'format' => 'sid',
            'enable_syno_token' => $loginItem->getVersion() >= 6 ? 'yes' : null,
            'session' => 'FileStation',
        ]));

        if ($result->containsKey('sid')) {
            $this->client->setSessionId($result->get('sid'));
        }

        if ($result->containsKey('synotoken')) {
            $this->client->setSynologyToken($result->get('synotoken'));
        }
    }

    private function resetTokens(): void
    {
        $this->client->setSessionId(null);
        $this->client->setSynologyToken(null);
    }
}
