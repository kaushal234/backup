<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\Warehousing\PriceBookLineDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PriceBookLineDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testMipIsDenormalized()
    {
        $mipDenormalizer = new PriceBookLineDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'value' => '145.33',
            'currency' => 'USD',
            'effectiveDate' => '2022-07-21T22:00:00Z',
            'expiryDate' => '2023-07-21T22:00:00Z',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['PRICE_BOOK_LINE_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $mipDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'value' => '145.33',
            'currency' => 'USD',
            'effectiveDate' => '2022-07-21T22:00:00Z',
            'expiryDate' => '2023-07-21T22:00:00Z',
        ];
        $context = [];
        $denormalizedData = $mipDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }
}
