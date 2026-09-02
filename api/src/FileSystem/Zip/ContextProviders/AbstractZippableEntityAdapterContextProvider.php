<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders;

use App\FileSystem\Zip\Report\ZipReport;
use App\FileSystem\Zip\ZippableEntityInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractZippableEntityAdapterContextProvider implements ZipAdapterContextProviderInterface
{
    private readonly ParameterBagInterface $parameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function processZip(\ZipArchive $zip, array $files, ZipReport $report, bool $flat = false)
    {
        foreach ($files as $zipName => $realPath) {
            $report->addFile($zip, $realPath, $zipName);
        }
    }

    /**
     * @param ZippableEntityInterface $object
     */
    public function getFiles($object, array $formats = []): array
    {
        $objectFiles = $object->getZippableFiles();
        $files = [];

        foreach ($objectFiles as $file) {
            $filename = pathinfo((string) $file->getFilePath(), \PATHINFO_BASENAME);

            $files[$filename] = $this->parameters->get('legacy.upload_dir').'/'.$file->getFilePath();
        }

        return $files;
    }
}
