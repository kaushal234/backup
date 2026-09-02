<?php

declare(strict_types=1);

namespace App\Tests\SageParts\Serializer\Denormalizer;

use App\SageParts\P21\Serializer\Denormalizer\SupplierDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SupplierDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupplierIsDenormalized()
    {
        $denormalizer = new SupplierDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'code' => '12345',
            'name' => 'SUPPLIER',
            'buyerEmail' => 'buyer@sage.com',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'json', ['SAGE_SUPPLIER_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $denormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'SupplierId' => '12345',
            'supplier_name' => 'SUPPLIER',
            'SageBuyerEmail' => 'buyer@sage.com',
        ];
        $context = [];
        $denormalizedData = $denormalizer->denormalize($data, 'type', 'json', $context);
        self::assertSame($expectedData, $denormalizedData);
    }
}
