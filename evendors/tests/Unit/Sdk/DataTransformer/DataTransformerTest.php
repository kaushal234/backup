<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer;

use App\Sdk\DataTransformer\DataTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Exception\NoSupportiveResourceTransformerException;
use App\Sdk\Page;
use App\Sdk\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Psl\Collection\Vector;
use Psl\Str;

/**
 * @group unit
 */
final class DataTransformerTest extends TestCase
{
    private DataTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new DataTransformer();
    }

    public function testTransform(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $resourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $this->transformer->addResourceTransformer($versionIncompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($incompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($resourceTransformer);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supports')->with($resource::class, $data)->willReturn(false);

        $resourceTransformer->expects($this->once())->method('supports')->with($resource::class, $data)->willReturn(true);
        $resourceTransformer->expects($this->once())->method('transform')->with($data)->willReturn($resource);

        self::assertSame($resource, $this->transformer->transform($resource::class, $data));
    }

    public function testTransformCollection(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $resourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $this->transformer->addResourceTransformer($versionIncompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($incompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($resourceTransformer);

        $resource = $this->createMock(ResourceInterface::class);
        $collection = new Vector([$resource]);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supportsCollection')->with($resource::class, $data)->willReturn(false);

        $resourceTransformer->expects($this->once())->method('supportsCollection')->with($resource::class, $data)->willReturn(true);
        $resourceTransformer->expects($this->once())->method('transformCollection')->with($data)->willReturn($collection);

        self::assertSame($collection, $this->transformer->transformCollection($resource::class, $data));
    }

    public function testTransformPage(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $resourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $this->transformer->addResourceTransformer($versionIncompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($incompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($resourceTransformer);

        $resource = $this->createMock(ResourceInterface::class);
        $collection = new Vector([$resource]);
        $page = new Page(1, 1, 1, false, false, $collection);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supportsPage')->with($resource::class, $data)->willReturn(false);

        $resourceTransformer->expects($this->once())->method('supportsPage')->with($resource::class, $data)->willReturn(true);
        $resourceTransformer->expects($this->once())->method('transformPage')->with($data, 1, 1)->willReturn($page);

        self::assertSame($page, $this->transformer->transformPage($resource::class, $data, 1, 1));
    }

    public function testTransformFailsForNoSupportiveResourceTransformer(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $this->transformer->addResourceTransformer($versionIncompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($incompatibleResourceTransformer);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supports')->with($resource::class, $data)->willReturn(false);

        $this->expectException(NoSupportiveResourceTransformerException::class);
        $this->expectExceptionMessage(Str\format('No supportive resource transformer is found for "%s" resource.', $resource::class));

        $this->transformer->transform($resource::class, $data);
    }

    public function testTransformCollectionFails(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $this->transformer->addResourceTransformer($versionIncompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($incompatibleResourceTransformer);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supportsCollection')->with($resource::class, $data)->willReturn(false);

        $this->expectExceptionMessage(Str\format('No supportive resource transformer is found for "%s" resource.', $resource::class));

        $this->transformer->transformCollection($resource::class, $data);
    }

    public function testTransformPageFails(): void
    {
        $versionIncompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);
        $incompatibleResourceTransformer = $this->createMock(ResourceTransformerInterface::class);

        $this->transformer->addResourceTransformer($versionIncompatibleResourceTransformer);
        $this->transformer->addResourceTransformer($incompatibleResourceTransformer);

        $resource = $this->createMock(ResourceInterface::class);
        $data = ['foo' => 'bar'];

        $incompatibleResourceTransformer->expects($this->once())->method('supportsPage')->with($resource::class, $data)->willReturn(false);

        $this->expectExceptionMessage(Str\format('No supportive resource transformer is found for "%s" resource.', $resource::class));

        $this->transformer->transformPage($resource::class, $data, 1, 1);
    }
}
