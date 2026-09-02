<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\Manual;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocumentFile;
use App\FileSystem\VaultPartFileProvider;
use App\FileSystem\Zip\ContextProviders\ZipAdapterContextProviderInterface;
use App\FileSystem\Zip\ProcessZipSimpleFolderTrait;
use App\Formatter\Snappy\ManualChapter4PdfGenerator;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ManualZipAdapterContextProvider implements ZipAdapterContextProviderInterface
{
    use ProcessZipSimpleFolderTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManualChapter4PdfGenerator $manualChapter4PdfGenerator,
        private readonly VaultPartFileProvider $vaultPartFileProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly ParameterBagInterface $parameters)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param Manual $subject
     */
    public function getFiles($subject, $formats = [], ?string $method = null): array
    {
        // Get files attached to ManualDocument of category 'MANUAL SECTION' (chapters 0, 1, 2, 3)
        $manualDocumentFiles = $this->entityManager->getRepository(ManualDocumentFile::class)->getManualSectionFiles($subject);

        $slugger = new AsciiSlugger();

        /** @var ManualDocumentFile $file */
        foreach ($manualDocumentFiles as $file) {
            $filename = \sprintf('%s.%s', $slugger->slug($file->getManualDocument()->description), $file->getExtension());

            if ('' !== ($slugCategory = mb_strtolower($slugger->slug($file->getManualDocument()->category->name ?? '')->toString()))) {
                $filename = \sprintf('%s_%s', $slugCategory, $filename);
            }

            $files[$filename] = $this->parameters->get('legacy.upload_dir').'/'.$file->getFilePath();
        }

        // Get a merged PDF of all files attached to ManualDocument with category equals 'PART DIAGRAMS'
        $chapter4Pdf = $this->manualChapter4PdfGenerator->generateChapter4PdfFile($subject);
        $files['chapter-4.pdf'] = $chapter4Pdf->getRealPath();

        // If Manual has an EquipmentRecord (legacy imported manuals may not have one),
        // get files attached to ER serials of the manual, with component equals 'SCHEMATICS'
        if ((null !== $subject->equipmentRecord) && (null !== $subject->equipmentRecord->getManufacturerLocation()) && (null !== $subject->equipmentRecord->getManufacturerLocation()->getErp())) {
            $schematics = $this->entityManager->getRepository(EquipmentSerial::class)->getSchematicsForEquipmentRecord($subject->equipmentRecord);
            $schematics = array_unique(array_column($schematics, 'serial'));

            $identifier = [
                'site' => (int) $subject->equipmentRecord->getManufacturerLocation()->getErp(),
                'project' => $subject->equipmentRecord->getProjectNumber(),
            ];
            $date = $subject->equipmentRecord->getGreenTagDate() ?? new \DateTime();
            $filters = [
                'date' => $date->format(\DateTimeInterface::ATOM),
                'signalCodeFilter' => 'ESC|HSC|PSC|BSC|FLD|RTD|PPD|PRG|PRM|GAD',
                'signalCodeFilterMethod' => 'Equals',
                'signalCodeAttribute' => 'engineeringSignalCode',
            ];

            $context = [
                DataAreaFilter::CONTEXT_DATA_AREA_KEY => $filters,
            ];

            try {
                $metadata = $this->resourceMetadataCollectionFactory->create(Manuals::class);
                /** @var Manuals $customizedBillOfMaterialsSchematics */
                $customizedBillOfMaterialsSchematics = $this->itemDataProvider->provide($metadata->getOperation(), $identifier, $context);
            } catch (SoapException) {
                $customizedBillOfMaterialsSchematics = null;
            }

            if (null !== $customizedBillOfMaterialsSchematics) {
                foreach ($customizedBillOfMaterialsSchematics->getItems() as $item) {
                    if (!\in_array($item->partNumber, $schematics, true)) {
                        continue;
                    }

                    $file = $this->vaultPartFileProvider->getFile((int) $subject->equipmentRecord->getManufacturerLocation()->getErp(), $item->partNumber, $item->engineeringRevision, 'pdf');

                    if (!$file instanceof File) {
                        continue;
                    }

                    $files[$file->getBasename()] = $file->getRealPath();
                }
            }
        }

        return $files;
    }

    public static function getClass(): string
    {
        return Manual::class;
    }

    /** @param Manual $subject */
    public function getArchiveName(object $subject): string
    {
        return \sprintf('manual-%d', $subject->getId());
    }
}
