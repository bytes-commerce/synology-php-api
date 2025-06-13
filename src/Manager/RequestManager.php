<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Manager;

use BytesCommerce\SynologyApi\Enum\ThumbnailSizeEnum;
use BytesCommerce\SynologyApi\Exceptions\NoConnectionException;
use BytesCommerce\SynologyApi\Exceptions\UserAccessException;
use BytesCommerce\SynologyApi\Factory\EndpointDefinitionFactory;
use BytesCommerce\SynologyApi\Items\AbstractActionItem;
use BytesCommerce\SynologyApi\Items\CreateItem;
use BytesCommerce\SynologyApi\Items\DownloadItem;
use BytesCommerce\SynologyApi\Items\GetInfoItem;
use BytesCommerce\SynologyApi\Items\GetThumbItem;
use BytesCommerce\SynologyApi\Items\ListItem;
use BytesCommerce\SynologyApi\Items\ListShareItem;
use BytesCommerce\SynologyApi\Items\LoginItem;
use BytesCommerce\SynologyApi\Items\QueryItem;
use BytesCommerce\SynologyApi\Items\RenameItem;
use BytesCommerce\SynologyApi\Items\StartCopyMoveItem;
use BytesCommerce\SynologyApi\Items\StartItem;
use BytesCommerce\SynologyApi\Items\StatusItem;
use BytesCommerce\SynologyApi\Items\UploadItem;
use BytesCommerce\SynologyApi\Resource\Client;
use BytesCommerce\SynologyApi\Resource\EndpointProvider;
use Doctrine\Common\Collections\ArrayCollection;
use RuntimeException;
use SensitiveParameter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class RequestManager
{
    private const CACHE_DURATION = 86400;

    private array $webApiInformation = [];

    public function __construct(
        private readonly Client $client,
        private readonly EndpointProvider $endpointProvider,
        private readonly EndpointDefinitionFactory $definitionFactory,
        private readonly FilesystemAdapter $filesystemAdapter,
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
            'folder_path' => json_encode($sharePaths, \JSON_UNESCAPED_UNICODE),
            'name' => json_encode($filePaths, \JSON_UNESCAPED_UNICODE),
            'force_parent' => $recursive,
            'additional' => json_encode([
                'size',
                'time',
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
                'time',
                'type',
                'perm',
                'mount_point_type',
                'volume_status',
            ]),
        ]);
    }

    public function upload(string $fileTargetPath, string|UploadedFile $filePath, ?string $fileName = null): ArrayCollection
    {
        $webApi = $this->getApiActionItem('SYNO.FileStation.Upload', UploadItem::class);

        $uploadResponse = $this->request($webApi, array_reverse([
            'path' => $fileTargetPath,
            'create_parents' => 'true',
            'overwrite' => 'overwrite',
            'additional' => json_encode([
                'size',
                'time',
                'type',
            ]),
            'filename' => (string) $filePath,
        ]));

        if ($filePath instanceof UploadedFile) {
            try {
                $this->delete([sprintf('%s/%s', $fileTargetPath, $fileName === null ? $filePath->getClientOriginalName() : $fileName)]);
            } finally {
                $this->rename(
                    sprintf('%s/%s', $fileTargetPath, $filePath->getFilename()),
                    $fileName === null ? $filePath->getClientOriginalName() : $fileName,
                );
            }
        }

        return $uploadResponse;
    }

    public function rename(string $target, string $newFilename): ArrayCollection
    {
        $webApi = $this->getApiActionItem('SYNO.FileStation.Rename', RenameItem::class);

        return $this->request($webApi, [
            'path' => $target,
            'name' => json_encode($newFilename, \JSON_UNESCAPED_UNICODE),
            'additional' => json_encode([
                'real_path',
                'size',
                'type',
            ]),
        ]);
    }

    public function download(array $filePaths, string $mode, int $cacheDuration = self::CACHE_DURATION): ArrayCollection
    {
        $fileHash = hash('sha256', implode(',', $filePaths));

        return $this->filesystemAdapter->get('bc.synology_download.' . $fileHash, function (ItemInterface $item) use ($filePaths, $mode, $cacheDuration) {
            $item->expiresAfter($cacheDuration);
            $webApi = $this->getApiActionItem('SYNO.FileStation.Download', DownloadItem::class);

            return $this->request($webApi, [
                'path' => json_encode($filePaths, \JSON_UNESCAPED_UNICODE),
                'mode' => sprintf('"%s"', $mode),
            ]);
        });
    }

    public function thumbnail(string $filePath, ThumbnailSizeEnum $thumbnailSize = ThumbnailSizeEnum::SMALL, int $cacheDuration = self::CACHE_DURATION): ArrayCollection
    {
        $fileHash = hash('sha256', sprintf('%s-%s', $filePath, $thumbnailSize->value));
        $cacheKey = 'bc.synology_thumb.' . $fileHash;

        if ($cacheDuration === 0) {
            $this->filesystemAdapter->delete($cacheKey);
        }

        return $this->filesystemAdapter->get($cacheKey, function (ItemInterface $item) use ($filePath, $thumbnailSize, $cacheDuration) {
            $item->expiresAfter($cacheDuration);
            $webApi = $this->getApiActionItem('SYNO.FileStation.Thumb', GetThumbItem::class);

            return $this->request($webApi, [
                'path' => sprintf('"%s"', $filePath),
                'size' => $thumbnailSize->value,
                'rotate' => 0,
            ]);
        });
    }

    public function copyMove(array $pathsToMove, string $targetDirectory, bool $isCopy = false): ArrayCollection
    {
        Assert::allString($pathsToMove);
        Assert::allNotEmpty($pathsToMove);
        $webApi = $this->getApiActionItem('SYNO.FileStation.CopyMove', StartCopyMoveItem::class);

        return $this->request($webApi, [
            'path' => json_encode($pathsToMove, \JSON_UNESCAPED_UNICODE),
            'dest_folder_path' => sprintf('"%s"', $targetDirectory),
            'remove_src' => $isCopy ? 'false' : 'true',
        ]);
    }

    public function fileInfo(array $paths, int $cacheDuration = self::CACHE_DURATION): ArrayCollection
    {
        $fileHash = hash('sha256', implode(',', $paths));
        $cacheKey = 'bc.synology_getinfo.' . $fileHash;

        if ($cacheDuration === 0) {
            $this->filesystemAdapter->delete($cacheKey);
        }

        return $this->filesystemAdapter->get($cacheKey, function (ItemInterface $item) use ($paths, $cacheDuration) {
            $item->expiresAfter($cacheDuration);
            $webApi = $this->getApiActionItem('SYNO.FileStation.List', GetInfoItem::class);

            return $this->request($webApi, [
                'path' => json_encode($paths, \JSON_UNESCAPED_UNICODE),
                'additional' => json_encode([
                    'size',
                    'time',
                    'type',
                ]),
            ]);
        });
    }

    public function delete(array $paths, bool $recursive = false): ArrayCollection
    {
        Assert::allString($paths);
        Assert::allNotEmpty($paths);
        $webApi = $this->getApiActionItem('SYNO.FileStation.Delete', StartItem::class);

        foreach($paths as $path) {
            $fileHash = hash('sha256', implode(',', [$path]));
            $cacheKey = 'bc.synology_download.' . $fileHash;
            if ($this->filesystemAdapter->hasItem($cacheKey)) {
                $this->filesystemAdapter->delete($cacheKey);
            }
        }

        $startResponse = $this->request($webApi, [
            'path' => json_encode($paths, \JSON_UNESCAPED_UNICODE),
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
        $tokens = $this->filesystemAdapter->get('bc.synology_api.endpoints', function (ItemInterface $item): ArrayCollection {
            $item->expiresAfter(600);
            $loginItem = $this->getApiActionItem('SYNO.API.Auth', LoginItem::class);

            return $this->client->request($loginItem, array_filter([
                'account' => $this->username,
                'passwd' => $this->password,
                'format' => 'sid',
                'enable_syno_token' => $loginItem->getVersion() >= 6 ? 'yes' : null,
                'session' => 'FileStation',
            ]));
        });

        if ($tokens->containsKey('sid')) {
            $this->client->setSessionId($tokens->get('sid'));
        }

        if ($tokens->containsKey('synotoken')) {
            $this->client->setSynologyToken($tokens->get('synotoken'));
        }
    }

    private function resetTokens(): void
    {
        $this->client->setSessionId(null);
        $this->client->setSynologyToken(null);
    }
}
