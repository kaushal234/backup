<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http;

use App\Sdk\Http\ResourceSourceProvider\TechnicianOnCallResourceSourceProvider;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\TechnicianOnCall;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TechnicianOnCallResourceSourceProviderTest extends TestCase
{
    private TechnicianOnCallResourceSourceProvider $resourceProvider;

    protected function setUp(): void
    {
        $this->resourceProvider = new TechnicianOnCallResourceSourceProvider();
    }

    public function testSupports(): void
    {
        $this->assertFalse($this->resourceProvider->supports('incorrect'));
        $this->assertFalse($this->resourceProvider->supports(EquipmentRecord::class));
        $this->assertTrue($this->resourceProvider->supports(TechnicianOnCall::class));
    }

    public function testGetFindSource(): void
    {
        $httpSource = $this->resourceProvider->getFindSource([
            'resource_id' => '42',
        ]);

        $this->assertSame('GET', $httpSource->method);
        $this->assertSame('service/technician_on_calls/42', $httpSource->uri);
        $this->assertArrayHasKey('query', $httpSource->options);
        $this->assertArrayHasKey('normalizationGroups', $httpSource->options['query']);
        $this->assertGreaterThan(1, $httpSource->options['query']['normalizationGroups']);
    }

    public function testGetUpdateSource(): void
    {
        $this->assertNull($this->resourceProvider->getUpdateSource('none', []));
    }

    public function testGetPaginationSource(): void
    {
        $httpSource = $this->resourceProvider->getPaginationSource(10, 42, ['foo' => 'bar']);

        $this->assertSame('GET', $httpSource->method);
        $this->assertSame('service/technician_on_calls', $httpSource->uri);
        $this->assertArrayHasKey('query', $httpSource->options);
        $this->assertArrayHasKey('normalizationGroups', $httpSource->options['query']);
        $this->assertGreaterThan(1, $httpSource->options['query']['normalizationGroups']);
        $this->assertArrayHasKey('foo', $httpSource->options['query']);
        $this->assertSame('bar', $httpSource->options['query']['foo']);
    }

    public function testGetExportSource(): void
    {
        $httpSource = $this->resourceProvider->getExportSource('text/csv', ['columns' => 'id,title']);

        $this->assertSame('GET', $httpSource->method);
        $this->assertSame('service/technician_on_calls', $httpSource->uri);
        $this->assertSame(['Accept' => 'text/csv'], $httpSource->options['headers']);
        $this->assertFalse($httpSource->options['query']['pagination']);
        $this->assertSame('id,title', $httpSource->options['query']['columns']);
    }

    public function testGetResourceIri(): void
    {
        $this->assertSame('service/technician_on_calls', $this->resourceProvider->getResourceIri());
    }
}
