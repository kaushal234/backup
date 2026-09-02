<?php

declare(strict_types=1);

namespace App\Factory\Support;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentCategory;
use App\Entity\Support\ManualDocumentFile;
use App\Factory\VaultFileFactory;
use App\FileSystem\VaultPartFileProvider;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorPdf;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use App\ION\Resources\Manufacturing\JobShop\ManualCustomizedBillOfMaterials;
use Doctrine\ORM\EntityManagerInterface;

class ManualDocumentFactory
{
    private const TYPE_BY_CODE_GROUPS = [
        'chapters' => 'MANUAL:SECTION',
        'parts_book' => 'PARTS DIAGRAM',
    ];

    private readonly EntityManagerInterface $entityManager;
    private readonly VaultFileFactory $vaultFileFactory;
    private readonly ManualPartFactory $manualPartFactory;
    private VaultPartFileProvider $vaultPartFileProvider;

    public function __construct(EntityManagerInterface $entityManager, VaultFileFactory $vaultFileFactory, ManualPartFactory $manualPartFactory, VaultPartFileProvider $vaultPartFileProvider)
    {
        $this->entityManager = $entityManager;
        $this->vaultFileFactory = $vaultFileFactory;
        $this->manualPartFactory = $manualPartFactory;
        $this->vaultPartFileProvider = $vaultPartFileProvider;
    }

    public function createCollectionFromCustomizedBillOfMaterials(Manuals|ManualCustomizedBillOfMaterials $customizedBillOfMaterials, Manual $manual, string $group, bool $force = false, bool $useVaultFile = false): void
    {
        $position = $manual->getDocuments()->count() + 1;
        $doneItems = [];
        $cachedCategories = [];
        $manualDocumentRepository = $this->entityManager->getRepository(ManualDocumentCategory::class);

        foreach ($customizedBillOfMaterials->getItems() as $item) {
            if (\in_array($item->partNumber, $doneItems, true)) {
                continue;
            }
            $doneItems[] = $item->partNumber;

            if (\array_key_exists($item->engineeringSignalCode, $cachedCategories)) {
                $manualDocumentCategory = $cachedCategories[$item->engineeringSignalCode];
            } else {
                $manualDocumentCategory = $manualDocumentRepository->findOneBy(['name' => $item->signalCodeDescription]);
                $cachedCategories[$item->engineeringSignalCode] = $manualDocumentCategory;
            }

            $manualDocument = new ManualDocument();
            $manualDocument->manual = $manual;
            $manualDocument->factoryNumber = $item->partNumber;
            $manualDocument->revision = $item->engineeringRevision;
            $manualDocument->category = $manualDocumentCategory;
            $manualDocument->description = $item->itemDescription;
            $manualDocument->otherDescription = $item->itemOtherDescription;
            $manualDocument->quantity = (float) $item->quantity;
            $manualDocument->type = self::TYPE_BY_CODE_GROUPS[$group];
            $manualDocument->position = $position++;

            if (false === $force) {
                $this->vaultFileFactory->attach($manualDocument, $manual->equipmentRecord->getManufacturerLocation()->getErp(), ('chapters' === $group) ? 'pdf' : 'jpg', false);
            }

            if (true === $useVaultFile && null !== ($file = $this->vaultPartFileProvider->getFile($customizedBillOfMaterials->site, $item->partNumber, $item->engineeringRevision))) {
                $documentFile = new ManualDocumentFile();
                $documentFile->setFilePath($file->getPathname());

                $manualDocument->setDocument($documentFile);
            }

            $this->manualPartFactory->createCollectionFromCustomizedBillOfMaterialsItem($item, $manualDocument);
            $manual->addDocument($manualDocument);
        }
    }

    public function createFromBillOfMaterials(BillOfMaterialItem $item): ManualDocument
    {
        $manualDocumentRepository = $this->entityManager->getRepository(ManualDocumentCategory::class);
        $manualDocumentCategory = $manualDocumentRepository->findOneBy(['name' => $item->signalCodeDescription]);

        $manualDocument = new ManualDocument();
        $manualDocument->factoryNumber = $item->getPartNumber();
        $manualDocument->revision = $item->engineeringRevision;
        $manualDocument->category = $manualDocumentCategory;
        $manualDocument->description = $item->itemDescription;
        $manualDocument->otherDescription = $item->itemOtherDescription;
        $manualDocument->quantity = 0;
        $manualDocument->type = '';
        $manualDocument->position = 0;

        $this->manualPartFactory->createCollectionFromBillOfMaterialsItem($item, $manualDocument);

        return $manualDocument;
    }

    public function createFromBOMShopfloorPDF(ShopfloorPdf $item): ManualDocument
    {
        $manualDocumentRepository = $this->entityManager->getRepository(ManualDocumentCategory::class);
        $manualDocumentCategory = $manualDocumentRepository->findOneBy(['name' => $item->signalCodeDescription]);

        $manualDocument = new ManualDocument();
        $manualDocument->factoryNumber = $item->getPartNumber();
        $manualDocument->revision = $item->engineeringRevision;
        $manualDocument->category = $manualDocumentCategory;
        $manualDocument->description = $item->itemDescription;
        $manualDocument->otherDescription = $item->itemOtherDescription;
        $manualDocument->quantity = 0;
        $manualDocument->type = '';
        $manualDocument->position = 0;

        $this->manualPartFactory->createCollectionFromBOMShopfloorItem($item, $manualDocument);

        return $manualDocument;
    }
}
