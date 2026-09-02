<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\FileSystem\DrawingExtensionFactory;
use App\FileSystem\VaultPartFileProvider;
use App\FileSystem\Zip\Report\ZipReport;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetMultiLevelView;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetViewItem;
use App\ION\Resources\RevisionDateInterface;
use App\ION\Resources\SiteInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractMultipleFoldersEntityAdapterContextProvider implements ZipMultipleFoldersAdapterContextProviderInterface
{
    public function __construct(
        private readonly VaultPartFileProvider $vaultPartFileProvider,
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly PropertyAccessorInterface $propertyAccessor
    ) {
    }

    /**
     * @param SiteInterface $subject
     */
    public function getFiles($subject, array $formats = []): array
    {
        $files = [];
        $productsAlreadyCalled = [];
        foreach ($this->propertyAccessor->getValue($subject, $this->getIterablePropertyPath()) as $data) {
            if (!$data instanceof RevisionDateInterface) {
                throw new UnprocessableEntityHttpException(\sprintf('Object must implement interface %s', RevisionDateInterface::class));
            }
            $product = $this->propertyAccessor->getValue($data, $this->getFilenamePropertyPath());
            if (\in_array($product, $productsAlreadyCalled, true)) {
                continue;
            }
            $productsAlreadyCalled[] = $product;

            $filters = ['depth' => 20];
            if (null !== $data->getDate()) {
                $filters['date'] = $data->getDate();
            }

            $metadata = $this->resourceMetadataFactory->create(IntranetMultiLevelView::class);
            /** @var IntranetMultiLevelView $billOfMaterials */
            $billOfMaterials = $this->itemDataProvider->provide($metadata->getOperation(), ['product' => $product, 'site' => $subject->getSiteNumber(), 'project' => ''], [DataAreaFilter::CONTEXT_DATA_AREA_KEY => $filters]);

            $partNumberSchematics = [];
            /** @var IntranetViewItem $item */
            foreach ($billOfMaterials->getItems() as $item) {
                $partNumberSchematics = $this->getPartNumbers($item, $partNumberSchematics, $product, $billOfMaterials->getSite());
            }

            if (null !== ($file = $this->vaultPartFileProvider->getPartFile($billOfMaterials))) {
                $partNumberSchematics = [$file->getRealPath(), ...$partNumberSchematics];
            }

            if (empty($partNumberSchematics)) {
                continue;
            }

            $files[$product] = $partNumberSchematics;
        }

        return $files;
    }

    public function processZip(\ZipArchive $zip, array $files, ZipReport $report, bool $flat = false): void
    {
        foreach ($files as $name => $folder) {
            if (empty($folder)) {
                continue;
            }
            $zip->addEmptyDir((string) $name);
            foreach ($folder as $realPath) {
                $filename = explode('/', $realPath);
                $zipPath = \sprintf('%s/%s', $name, end($filename));

                $report->addFile($zip, $realPath, $zipPath);
            }
        }
    }

    protected function getPartNumbers(IntranetViewItem $cbom, array $parentPartNumbers, string $partNumber, int $erp): array
    {
        if (null !== $cbom->engineeringRevisionDrawing) {
            $file = $this->vaultPartFileProvider->getFileByName($erp, $cbom->engineeringRevisionDrawing);
        } else {
            $file = $this->vaultPartFileProvider->getFile($erp, $cbom->partNumber, $cbom->engineeringRevision, DrawingExtensionFactory::getExtension($cbom->itemSignalCode));
        }

        do {
            if (null !== $file) {
                $parentPartNumbers[] = $file->getRealPath();
            }
            foreach ($cbom->getItems() as $child) {
                return $this->getPartNumbers($child, $parentPartNumbers, $partNumber, $erp);
            }
        } while (!$cbom->getItems()->isEmpty());

        return $parentPartNumbers;
    }
}
