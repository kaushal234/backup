<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\IonTextByLanguageDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IonTextByLanguageDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testTextsByLanguageAreDenormalized()
    {
        $textItemDenormalizer = new IonTextByLanguageDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'lang' => '2',
            'name' => 'English',
            'text' => 'Pas changé assiete pour fromagePas changé assiete pour fromage 2',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['ION_TEXT_BY_LANGUAGE_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $textItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'lang' => '2  ',
            'name' => '   English',
            'texts' => [
                'text' => [
                    0 => 'Pas changé assiete pour fromage',
                    1 => 'Pas changé assiete pour fromage 2',
                ],
            ],
        ];
        $context = [];

        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }

    public function testOneTextByLanguageIsDenormalized()
    {
        $textItemDenormalizer = new IonTextByLanguageDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'lang' => '2',
            'name' => 'English',
            'text' => 'Pas changé assiete pour fromage',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['ION_TEXT_BY_LANGUAGE_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $textItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'lang' => '2  ',
            'name' => '   English',
            'texts' => [
                'text' => 'Pas changé assiete pour fromage',
            ],
        ];
        $context = [];

        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }
}
