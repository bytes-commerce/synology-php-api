<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Tests\Unit\Resource;

use BytesCommerce\SynologyApi\Exceptions\UserAccessException;
use BytesCommerce\SynologyApi\Items\AbstractActionItem;
use BytesCommerce\SynologyApi\Resource\Client;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ClientTest extends TestCase
{
    private Client $client;

    private HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->client = new Client(
            'https://example.com',
            'https://example.com',
            $this->httpClient,
        );
    }

    public function test_set_and_get_synology_token(): void
    {
        $token = 'testToken';
        $this->client->setSynologyToken($token);
        $this->assertSame($token, $this->client->getSynologyToken());
    }

    public function test_set_and_get_session_id(): void
    {
        $sessionId = 'session123';
        $this->client->setSessionId($sessionId);
        $this->assertSame($sessionId, $this->client->getSessionId());
    }

    public function test_has_tokens_returns_true_if_both_are_set(): void
    {
        $this->client->setSessionId('session123');
        $this->client->setSynologyToken('token123');
        $this->assertTrue($this->client->hasTokens());
    }

    public function test_has_tokens_returns_false_if_one_is_missing(): void
    {
        $this->client->setSessionId('session123');
        $this->assertFalse($this->client->hasTokens());
    }

    public function test_request_success_returns_array_collection(): void
    {
        $actionItem = $this->createMock(AbstractActionItem::class);
        $actionItem->method('getApi')->willReturn('SYNO.Fake.API');
        $actionItem->method('getVersion')->willReturn(1);
        $actionItem->method('getMethod')->willReturn('get');
        $actionItem->method('getPath')->willReturn('FakePath.cgi');
        $actionItem->method('isMultipartForm')->willReturn(false);
        $actionItem->method('getAvailableKeys')->willReturn(['key1', 'key2']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn(json_encode(['success' => true, 'data' => ['foo' => 'bar']]));

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $result = $this->client->request($actionItem, ['key1' => 'value1']);

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertSame('bar', $result->get('foo'));
    }

    public function test_request_throws_runtime_exception_on_http_error(): void
    {
        $this->expectException(\RuntimeException::class);

        $actionItem = $this->createMock(AbstractActionItem::class);
        $actionItem->method('getApi')->willReturn('SYNO.API');
        $actionItem->method('getVersion')->willReturn(1);
        $actionItem->method('getMethod')->willReturn('get');
        $actionItem->method('getPath')->willReturn('Auth.cgi');
        $actionItem->method('isMultipartForm')->willReturn(false);
        $actionItem->method('getAvailableKeys')->willReturn([]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $this->httpClient->method('request')->willReturn($response);

        $this->client->request($actionItem);
    }

    public function test_request_throws_user_access_exception(): void
    {
        $this->expectException(UserAccessException::class);

        $actionItem = $this->createMock(AbstractActionItem::class);
        $actionItem->method('getApi')->willReturn('SYNO.API');
        $actionItem->method('getVersion')->willReturn(1);
        $actionItem->method('getMethod')->willReturn('get');
        $actionItem->method('getPath')->willReturn('Auth.cgi');
        $actionItem->method('isMultipartForm')->willReturn(false);
        $actionItem->method('getAvailableKeys')->willReturn([]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn(json_encode(['success' => false, 'error' => ['code' => 119]]));

        $this->httpClient->method('request')->willReturn($response);

        $this->client->request($actionItem);
    }
}
