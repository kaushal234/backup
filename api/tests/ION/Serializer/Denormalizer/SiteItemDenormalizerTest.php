<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\Warehousing\SiteItemDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SiteItemDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testSiteItemIsDenormalized()
    {
        $siteItemDenormalizer = new SiteItemDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'site' => 'item123',
            'weight' => 3.0,
            'weightUnitOfMeasure' => 'desTonnesDeLitre',
            'property' => 'value',
            'codeSignal' => '***',
            'warehouses' => [
                ['code' => '123'],
            ],
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['SITE_ITEM_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $siteItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'site' => '   item123    ',
            'weight' => '3',
            'weightUnitOfMeasure' => '    desTonnesDeLitre     ',
            'itemCodeSignal' => '  ***',
            'warehouses' => [
                'warehouse' => [
                    'code' => '123',
                ],
            ],
            'property' => 'value',
        ];
        $context = [];
        $denormalizedData = $siteItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }
}
