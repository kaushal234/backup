<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer;

use App\Sdk\DataTransformer\DataTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Exception\NoSupportiveResourceTransformerException;
use App\Sdk\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Psl\Collection\Vector;
use Psl\Str;

/**
 * @group unit
 */
final class DataTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $resourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supports')->with($resource::class, $data)->willReturn(false);

        $resourceTransformer->expects($this->once())->method('supports')->with($resource::class, $data)->willReturn(true);
        $resourceTransformer->expects($this->once())->method('transform')->with($data)->willReturn($resource);

        $transformer = new DataTransformer([$versionIncompatibleResourceTransformer, $incompatibleResourceTransformer, $resourceTransformer]);
        self::assertSame($resource, $transformer->transform($resource::class, $data));
    }

    public function testTransformCollection(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $resourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $resource = $this->createMock(ResourceInterface::class);
        $collection = new Vector([$resource]);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supportsCollection')->with($resource::class, $data)->willReturn(false);

        $resourceTransformer->expects($this->once())->method('supportsCollection')->with($resource::class, $data)->willReturn(true);
        $resourceTransformer->expects($this->once())->method('transformCollection')->with($data)->willReturn($collection);

        $transformer = new DataTransformer([$versionIncompatibleResourceTransformer, $incompatibleResourceTransformer, $resourceTransformer]);
        self::assertSame($collection, $transformer->transformCollection($resource::class, $data));
    }

    public function testTransformFailsForNoSupportiveResourceTransformer(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supports')->with($resource::class, $data)->willReturn(false);

        $this->expectException(NoSupportiveResourceTransformerException::class);
        $this->expectExceptionMessage(Str\format('No supportive resource transformer is found for "%s" resource.', $resource::class));

        $transformer = new DataTransformer([$versionIncompatibleResourceTransformer, $incompatibleResourceTransformer]);
        $transformer->transform($resource::class, $data);
    }

    public function testTransformCollectionFails(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supportsCollection')->with($resource::class, $data)->willReturn(false);

        $this->expectExceptionMessage(Str\format('No supportive resource transformer is found for "%s" resource.', $resource::class));

        $transformer = new DataTransformer([$versionIncompatibleResourceTransformer, $incompatibleResourceTransformer]);
        $transformer->transformCollection($resource::class, $data);
    }
}
