<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\BillOfMaterial;

use App\FileSystem\VaultPartFileProvider;
use App\FileSystem\Zip\ContextProviders\ZipMultipleFoldersAdapterContextProviderInterface;
use App\FileSystem\Zip\Report\ZipReport;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterialsItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BillOfMaterialItemZipAdapterContextProvider implements ZipMultipleFoldersAdapterContextProviderInterface
{
    private const CHILDREN = 'children';

    public function __construct(protected VaultPartFileProvider $vaultPartFileProvider)
    {
    }

    public static function getClass(): string
    {
        return BillOfMaterialItem::class;
    }

    public function getIterablePropertyPath(): string
    {
        return 'items';
    }

    public function getFilenamePropertyPath(): string
    {
        return 'partNumber';
    }

    /** @param BillOfMaterialItem $subject */
    public function getArchiveName(object $subject): string
    {
        return \sprintf('bom-%s', $subject->getPartNumber());
    }

    /**
     * @param BillOfMaterialItem $object
     */
    public function getFiles($object, array $formats = []): array
    {
        $files = [];
        $site = $object->site;

        if (null !== ($file = $this->vaultPartFileProvider->getPartFile($object, null, true))) {
            $files[$object->getPartNumber()] = [$file->getRealPath()];
            foreach ($formats as $format) {
                if (null !== ($file = $this->vaultPartFileProvider->getPartFile($object, $format, true))) {
                    $files[$object->getPartNumber()][] = $file->getRealPath();
                }
            }
        }

        if ($object->getItems()->count()) {
            $files[$object->getPartNumber()][self::CHILDREN] = [];
            $this->childrenFiles($object, $files[$object->getPartNumber()][self::CHILDREN], $site, $formats);
        }

        return $files;
    }

    public function processZip(
        \ZipArchive $zip,
        array $files,
        ZipReport $report,
        bool $flat = false,
        string $rootFolderName = ''
    ): void {
        foreach ($files as $name => $folder) {
            if (empty($folder)) {
                continue;
            }

            $folderName = $flat ? $rootFolderName : (empty($rootFolderName) ? (string) $name : \sprintf('%s/%s', $rootFolderName, $name));

            if (!$flat && !empty($folderName)) {
                $zip->addEmptyDir($folderName);
            }

            foreach ($folder as $key => $realPath) {
                if (self::CHILDREN === $key) {
                    $this->processZip($zip, $realPath, $report, $flat, $folderName);
                    continue;
                }

                $filename = basename($realPath);
                $zipPath = $flat ? $filename : \sprintf('%s/%s', $folderName, $filename);

                $report->addFile($zip, $realPath, $zipPath);
            }
        }
    }

    private function childrenFiles($item, array &$files, int $site, array $formats = []): void
    {
        foreach ($this->getChildren($item) as $data) {
            $product = $data->partNumber;
            $data->setSite($site);

            if (null !== ($file = $this->vaultPartFileProvider->getPartFile($data, null, true))) {
                $files[$product] = [$file->getRealPath()];
                foreach ($formats as $format) {
                    if (null !== ($file = $this->vaultPartFileProvider->getPartFile($data, $format, true))) {
                        $files[$product][] = $file->getRealPath();
                    }
                }
            }

            if ($data->getChildren()->count()) {
                $files[$product][self::CHILDREN] = [];
                $this->childrenFiles($data, $files[$product][self::CHILDREN], $site, $formats);
            }
        }
    }

    private function getChildren($object): Collection
    {
        if ($object instanceof BillOfMaterialItem) {
            return $object->getItems();
        }

        if ($object instanceof CustomizedBillOfMaterialsItem) {
            return $object->getChildren();
        }

        return new ArrayCollection();
    }
}
