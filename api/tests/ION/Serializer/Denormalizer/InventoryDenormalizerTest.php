<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\FileSystem\Image\VaultPartImageFileProvider;
use App\ION\Serializer\Denormalizer\Warehousing\InventoryDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InventoryDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testInventoryIsDenormalized()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $vaultProviderProphecy = $this->prophesize(VaultPartImageFileProvider::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);

        $partsPictureFile = new File('tests/fixtures/image_1200x1200.jpg');
        $vaultProviderProphecy
            ->getAll('item123')
            ->shouldBeCalledOnce()
            ->willReturn([$partsPictureFile]);

        $routerProphecy
            ->generate('manufacturing_inventory_image', ['item' => 'item123', 'index' => 1])
            ->shouldBeCalledOnce()
            ->willReturn('test_route_picture_result');

        $serviceLocatorProphecy
            ->get(VaultPartImageFileProvider::class)
            ->shouldBeCalledOnce()
            ->willReturn($vaultProviderProphecy->reveal());

        $serviceLocatorProphecy
            ->get(UrlGeneratorInterface::class)
            ->shouldBeCalledOnce()
            ->willReturn($routerProphecy->reveal());

        $inventoryDenormalizer = new InventoryDenormalizer($serviceLocatorProphecy->reveal());

        // expected final output
        $expectedData = [
            'item' => 'item123',
            'unitOfMeasure' => 'litre',
            'productLine' => 'untrimmed productLine',
            'productClass' => [
                'code' => '  1    ',
                'name' => 'untrimmed productClass   ',
            ],
            'priceBook' => 'SL0000002',
            'pmoc' => 'pmoc',
            'property' => 'value',
            'priceBookLines' => [
                [
                    'value' => '503.82',
                    'currency' => 'CNY',
                    'effectiveDate' => '2022-07-12T22:00:00Z',
                    'expiryDate' => '2023-07-12T22:00:00Z',
                ],
            ],
            'description' => 'itemDescription123',
            'siteItems' => [
                ['site' => '123'],
            ],
            'pictures' => [[
                'key' => 1,
                'uri' => 'test_route_picture_result',
                'filename' => 'image_1200x1200.jpg',
                'extension' => 'jpg',
                'size' => 20077,
            ]],
        ];

        // denormalizer mock (Prophecy)
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);

        $denormalizerProphecy
            ->denormalize(
                Argument::that(static function ($arg) use ($expectedData) {
                    return \is_array($arg)
                        && ($arg['item'] ?? null) === $expectedData['item']
                        && isset($arg['pictures'][0]['filename']);
                }),
                'type',
                'xml',
                Argument::that(static function ($ctx) {
                    return isset($ctx['INVENTORY_DENORMALIZER_ALREADY_CALLED'])
                        && true === $ctx['INVENTORY_DENORMALIZER_ALREADY_CALLED'];
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn($expectedData);

        $inventoryDenormalizer->setDenormalizer($denormalizerProphecy->reveal());

        // raw input data (messy, trimmed inside denormalizer)
        $data = [
            'item' => '   item123    ',
            'itemDescription' => '   itemDescription123     ',
            'unitOfMeasure' => '   litre  ',
            'productLine' => '   untrimmed productLine                    ',
            'productClass' => [
                'code' => '  1    ',
                'name' => 'untrimmed productClass   ',
            ],
            'priceBook' => 'SL0000002',
            'itemBySite' => [
                'site' => '123',
            ],
            'pmoc' => 'pmoc    ',
            'property' => 'value',
            'priceBookLines' => [
                'mip' => [
                    [
                        'value' => '503.82',
                        'currency' => 'CNY',
                        'effectiveDate' => '2022-07-12T22:00:00Z',
                        'expiryDate' => '2023-07-12T22:00:00Z',
                    ],
                ],
            ],
        ];

        $context = [];

        $denormalizedData = $inventoryDenormalizer->denormalize($data, 'type', 'xml', $context);

        self::assertSame($expectedData, $denormalizedData);
    }
}
