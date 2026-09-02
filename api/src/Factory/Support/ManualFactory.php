<?php

declare(strict_types=1);

namespace App\Factory\Support;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\EquipmentRecord;
use App\Entity\Support\Manual;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use App\ION\Resources\Manufacturing\JobShop\ManualCustomizedBillOfMaterials;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ManualFactory
{
    final public const PRELIMINARY = 'PRELIMINARY';
    final public const PARTS_BOOK = 'parts_book';
    final public const CHAPTERS = 'chapters';

    final public const SIGNAL_CODES = [
        'parts_book' => [
            'SPA', 'SPB', 'SPC', 'SPD', 'SPE', 'SPF', 'SPG', 'SPH', 'SPI', 'SPJ', 'SPK', 'SPL', 'SPM',
            'SPN', 'IGA', 'IGB', 'IGC', 'IGD', 'IGE', 'IGF', 'IGG', 'IGH', 'IGI', 'IGJ', 'IGK', 'IGL',
            'IGM', 'IGN', 'IEA', 'IEB', 'IEC', 'IED', 'IEE', 'IEF', 'IEG', 'IEH', 'IEI', 'IEJ', 'IEK',
            'IEL', 'IEM', 'IEN', 'IHA', 'IHB', 'IHC', 'IHD', 'IHE', 'IHF', 'IHG', 'IHH', 'IHI', 'IHJ',
            'IHK', 'IHL', 'IHM', 'IHN', 'IMA', 'IMB', 'IMC', 'IMD', 'IME', 'IMF', 'IMG', 'IMH', 'IMI',
            'IMJ', 'IMK', 'IML', 'IMM', 'IMN',
        ],
        'assembly_instructions' => [
            'AIM', 'AIE', 'AIH', 'AIG',
        ],
        'schematics' => [
            'ESC', 'HSC', 'PSC', 'BSC', 'FLD', 'RTD', 'PPD', 'PRG', 'PRM', 'GAD',
        ],
        'chapters' => [
            'CH0', 'CH1', 'CH2', 'CH3', 'CH5',
        ],
    ];

    public function __construct(
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly ManualDocumentFactory $manualDocumentFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    public static function getFormattedGroupSignaCode(string $group): string
    {
        return implode('|', self::SIGNAL_CODES[$group] ?? []);
    }

    public function create(EquipmentRecord $equipmentRecord, string $language = 'en', bool $force = false, string $status = 'RELEASED'): Manual
    {
        $manual = new Manual();
        $manual->equipmentRecord = $equipmentRecord;
        $manual->language = $language;
        $manual->features = $equipmentRecord->getOptionsDescription();
        $manual->description = $this->generateDescription($equipmentRecord);

        $signalCodesGroupList = self::PRELIMINARY === $status ? [self::CHAPTERS] : [self::CHAPTERS, self::PARTS_BOOK];
        $projectExist = false;

        $metadata = $this->resourceMetadataCollectionFactory->create(Manuals::class);

        foreach ($signalCodesGroupList as $signalCodeGroup) {
            /** @var \DateTime $date */
            $date = ('chapters' !== $signalCodeGroup && $manual->equipmentRecord->getGreenTagDate()) ? $manual->equipmentRecord->getGreenTagDate() : new \DateTime();
            $date->setTime(23, 59, 59);

            $customizedBillOfMaterials = $this->itemDataProvider->provide(
                $metadata->getOperation(),
                [
                    'site' => $equipmentRecord->getManufacturerLocation()->getErp(),
                    'project' => $manual->equipmentRecord->getProjectNumber(),
                ],
                [
                    'operation_type' => Get::class,
                    DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                        'date' => $date->format(\DateTimeInterface::ATOM),
                        'signalCodeFilter' => self::getFormattedGroupSignaCode($signalCodeGroup),
                        'signalCodeFilterMethod' => 'Equals',
                        'signalCodeAttribute' => 'engineeringSignalCode',
                        'otherLanguage' => $language,
                    ],
                ]
            );

            if (!$customizedBillOfMaterials instanceof Manuals) {
                continue;
            }

            $projectExist = true;
            $this->manualDocumentFactory->createCollectionFromCustomizedBillOfMaterials($customizedBillOfMaterials, $manual, $signalCodeGroup, $force);
        }

        if (!$projectExist) {
            throw new NotFoundHttpException('No CBOM found for this Equipment Record.');
        }

        return $manual;
    }

    public function createFromManualCustomizedBillOfMaterials(Manuals|ManualCustomizedBillOfMaterials $manualCustomizedBillOfMaterials): Manual
    {
        $manual = new Manual();

        $this->manualDocumentFactory->createCollectionFromCustomizedBillOfMaterials($manualCustomizedBillOfMaterials, $manual, self::PARTS_BOOK, true, true);

        return $manual;
    }

    public function cloneManual(Manual $manual, EquipmentRecord $equipmentRecord): Manual
    {
        $clonedManual = clone $manual;
        $clonedManual->equipmentRecord = $equipmentRecord;
        $clonedManual->description = $this->generateDescription($equipmentRecord);

        return $clonedManual;
    }

    private function generateDescription(EquipmentRecord $equipmentRecord): string
    {
        return \sprintf(
            '%s, %s <br> *** Automatically generated from CBOM#%s on the %s ***',
            $equipmentRecord->getType(),
            $equipmentRecord->getModel(),
            $equipmentRecord->getProjectNumber(),
            date('Y-m-d')
        );
    }
}
