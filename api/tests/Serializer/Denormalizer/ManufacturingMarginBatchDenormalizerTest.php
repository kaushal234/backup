<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Denormalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Finance\ManufacturingMarginBatch;
use App\Entity\Finance\ManufacturingMargin;
use App\Serializer\Denormalizer\ManufacturingMarginBatchDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ManufacturingMarginBatchDenormalizerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testSupportsDenormalization()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);

        self::bootKernel();

        $denormalizer = new ManufacturingMarginBatchDenormalizer($entityManagerProphecy->reveal(), $iriConverterProphecy->reveal(), self::getContainer()->get('array_cache'));
        self::assertTrue($denormalizer->supportsDenormalization([], ManufacturingMarginBatch::class));
        self::assertFalse($denormalizer->supportsDenormalization([], ManufacturingMarginBatch::class, null, ['MANUFACTURING_MARGIN_BATCH_ALREADY_CALLED' => true]));
        self::assertFalse($denormalizer->supportsDenormalization([], 'Bar'));
    }

    public function testDenormalize()
    {
        self::bootKernel();
        /** @var ManufacturingMarginBatchDenormalizer $serializer */
        $serializer = self::getContainer()->get('serializer');

        $marginArrayOne = ['equipmentRecord' => '37455', 'currency' => 'EUR', 'month' => '09', 'year' => '2020', 'standardHours' => 150.0];
        $marginArrayTwo = ['equipmentRecord' => '37469', 'currency' => 'USD', 'month' => '08', 'year' => '2019', 'standardHours' => 299.0];

        /** @var ManufacturingMarginBatch $manufacturingMarginBatch */
        $manufacturingMarginBatch = $serializer->denormalize(['margins' => [$marginArrayOne, $marginArrayTwo]], ManufacturingMarginBatch::class);

        /** @var ManufacturingMargin $marginOne */
        $marginOne = $manufacturingMarginBatch->getMargins()->first();
        /** @var ManufacturingMargin $marginTwo */
        $marginTwo = $manufacturingMarginBatch->getMargins()->last();

        self::assertSame(30.0, $marginOne->getOptionConfigurationParameterHours());
        self::assertSame('2020-09', $marginOne->getExportedAt()->format('Y-m'));
        self::assertSame('EUR', $marginOne->getCurrency()->getName());

        self::assertSame(0.0, $marginTwo->getOptionConfigurationParameterHours());
        self::assertSame('2019-08', $marginTwo->getExportedAt()->format('Y-m'));
        self::assertSame('USD', $marginTwo->getCurrency()->getName());
    }
}
