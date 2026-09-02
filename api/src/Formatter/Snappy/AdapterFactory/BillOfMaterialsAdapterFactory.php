<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\Support\Manual;
use App\Factory\Support\ManualDocumentFactory;
use App\FileSystem\VaultPartFileProvider;
use App\Formatter\Snappy\Adapter;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;

class BillOfMaterialsAdapterFactory extends ItemAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    public const PURPOSE = 'bill_of_materials';

    private ManualDocumentFactory $manualDocumentFactory;
    private VaultPartFileProvider $vaultPartFileProvider;

    public function __construct(ManualDocumentFactory $manualDocumentFactory, VaultPartFileProvider $vaultPartFileProvider)
    {
        $this->manualDocumentFactory = $manualDocumentFactory;
        $this->vaultPartFileProvider = $vaultPartFileProvider;
    }

    /**
     * @param BillOfMaterialItem $billOfMaterial
     */
    public function getAdapter($billOfMaterial, string $format): Adapter
    {
        $document = $this->manualDocumentFactory->createFromBillOfMaterials($billOfMaterial);
        if (null !== ($file = $this->vaultPartFileProvider->getPartFile($billOfMaterial, 'jpg'))) {
            $file = $file->getRealPath();
        }

        $manual = new Manual();
        $manual->language = 'ENGLISH';

        return $this->process($manual, $document, ['file' => $file, 'document_id' => 'ID']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof BillOfMaterialItem && self::PURPOSE === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}
