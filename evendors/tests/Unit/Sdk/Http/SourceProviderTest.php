<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http;

use App\Sdk\Exception\NoSupportiveResourceSourceProviderException;
use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\Sdk\Http\SourceProvider;
use App\Sdk\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Psl\Str;

/**
 * @group unit
 */
final class SourceProviderTest extends TestCase
{
    private SourceProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new SourceProvider();
    }

    public function testGetFindSources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $source = HttpSource::create('GET', '/foo', []);

        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $unsupportedOperationResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $incompatibleResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);

        $resourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $incompatibleResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(false);

        $resourceSourceProvider->expects($this->once())->method('getFindSource')->with('1')->willReturn($source);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('getFindSource')->with('1')->willReturn(null);

        $this->provider->addResourceSourceProvider($resourceSourceProvider);
        $this->provider->addResourceSourceProvider($unsupportedOperationResourceSourceProvider);
        $this->provider->addResourceSourceProvider($incompatibleResourceSourceProvider);

        $sources = $this->provider->getFindSources($resource::class, '1');

        self::assertCount(1, $sources);
        self::assertSame([$source], $sources);
    }

    public function testGetFindAllSources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $source = HttpSource::create('GET', '/foo', []);
        $distributedSource = DistributedHttpSource::combine(
            HttpSource::create('GET', '/bar', []),
            HttpSource::create('GET', '/baz', []),
        );

        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $distributedResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $unsupportedOperationResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $incompatibleResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);

        $resourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $distributedResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $incompatibleResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(false);

        $resourceSourceProvider->expects($this->once())->method('getFindAllSource')->willReturn($source);
        $distributedResourceSourceProvider->expects($this->once())->method('getFindAllSource')->willReturn($distributedSource);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('getFindAllSource')->willReturn(null);

        $this->provider->addResourceSourceProvider($resourceSourceProvider);
        $this->provider->addResourceSourceProvider($distributedResourceSourceProvider);
        $this->provider->addResourceSourceProvider($unsupportedOperationResourceSourceProvider);
        $this->provider->addResourceSourceProvider($incompatibleResourceSourceProvider);

        $sources = $this->provider->getFindAllSources($resource::class);

        self::assertCount(2, $sources);
        self::assertSame([$source, $distributedSource], $sources);
    }

    public function testGetDownloadSources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $source = HttpSource::create('GET', '/foo', []);

        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $unsupportedOperationResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $incompatibleResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);

        $resourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $incompatibleResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(false);

        $resourceSourceProvider->expects($this->once())->method('getDownloadSource')->with('1')->willReturn($source);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('getDownloadSource')->with('1')->willReturn(null);

        $this->provider->addResourceSourceProvider($resourceSourceProvider);
        $this->provider->addResourceSourceProvider($unsupportedOperationResourceSourceProvider);
        $this->provider->addResourceSourceProvider($incompatibleResourceSourceProvider);

        $sources = $this->provider->getDownloadSources($resource::class, '1');

        self::assertCount(1, $sources);
        self::assertSame([$source], $sources);
    }

    public function testGetPaginationSources(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $source = HttpSource::create('GET', '/foo', []);

        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $unsupportedOperationResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $incompatibleResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);

        $resourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(true);
        $incompatibleResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(false);

        $resourceSourceProvider->expects($this->once())->method('getPaginationSource')->with(1, 10)->willReturn($source);
        $unsupportedOperationResourceSourceProvider->expects($this->once())->method('getPaginationSource')->with(1, 10)->willReturn(null);

        $this->provider->addResourceSourceProvider($resourceSourceProvider);
        $this->provider->addResourceSourceProvider($unsupportedOperationResourceSourceProvider);
        $this->provider->addResourceSourceProvider($incompatibleResourceSourceProvider);

        $sources = $this->provider->getPaginationSources($resource::class, 1, 10);

        self::assertCount(1, $sources);
        self::assertSame([$source], $sources);
    }

    public function testGetSourcesFailsWhenNoProvidersAreAvailable(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $incompatibleResourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);

        $incompatibleResourceSourceProvider->expects($this->once())->method('supports')->with($resource::class)->willReturn(false);

        $this->provider->addResourceSourceProvider($incompatibleResourceSourceProvider);

        $this->expectException(NoSupportiveResourceSourceProviderException::class);
        $this->expectExceptionMessage(Str\format('No supportive resource request factory is found for "%s" resource.', $resource::class));

        $this->provider->getPaginationSources($resource::class, 1, 10);
    }
}
