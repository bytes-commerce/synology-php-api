<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Tests\Unit\Resource;

use BytesCommerce\SynologyApi\Resource\EndpointProvider;
use PHPUnit\Framework\TestCase;

final class EndpointProviderTest extends TestCase
{
    private EndpointProvider $endpointProvider;

    protected function setUp(): void
    {
        $this->endpointProvider = new EndpointProvider();
    }

    public function test_get_referer_removes_subdomain_correctly(): void
    {
        $inputUrl = 'https://abc.def.example.com';
        $expected = 'https://abc.example.com';
        $result = $this->endpointProvider->getReferer($inputUrl);
        $this->assertSame($expected, $result);
    }

    public function test_get_referer_returns_valid_referer(): void
    {
        $inputUrl = 'https://example.com';
        $expected = 'https://example.com';
        $result = $this->endpointProvider->getReferer($inputUrl);
        $this->assertSame($expected, $result);
    }
}
