<?php

declare(strict_types=1);

namespace App\Tests\SageParts\Serializer\Denormalizer;

use App\SageParts\P21\Serializer\Denormalizer\PartDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PartDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testPartIsDenormalized()
    {
        $denormalizer = new PartDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'item' => '12345',
            'itemDescription' => 'Item for phpunit tests',
            'unitOfMeasure' => 'EACH',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'json', ['SAGE_PART_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $denormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'ItemId' => '12345',
            'ItemDesc' => 'Item for phpunit tests',
            'UnitOfMeasure' => 'EACH',
        ];
        $context = [];
        $denormalizedData = $denormalizer->denormalize($data, 'type', 'json', $context);
        self::assertSame($expectedData, $denormalizedData);
    }
}
