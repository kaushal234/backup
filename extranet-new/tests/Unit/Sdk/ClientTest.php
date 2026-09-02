<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk;

use App\Sdk\Client;
use App\Sdk\DataTransformer\DataTransformer;
use App\Sdk\Exception\UnsupportedOperationException;
use App\Sdk\Http\Client as HttpClient;
use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\Sdk\Http\SourceProvider;
use App\Tests\Unit\DummyResource;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @group unit
 */
class ClientTest extends TestCase
{
    public function testExportThrowsWhenTheResourceHasNoExportSource(): void
    {
        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $resourceSourceProvider->method('getExportSource')->willReturn(null);

        $sourceProvider = $this->createMock(SourceProvider::class);
        $sourceProvider->method('getResourceSourceProvider')->with(DummyResource::class)->willReturn($resourceSourceProvider);

        $client = new Client(
            $this->createMock(HttpClient::class),
            $this->createMock(DataTransformer::class),
            $sourceProvider,
            $this->createMock(Security::class),
        );

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage(\sprintf('Resource "%s" does not support export operation.', DummyResource::class));

        $client->export(DummyResource::class, 'text/csv');
    }
}
