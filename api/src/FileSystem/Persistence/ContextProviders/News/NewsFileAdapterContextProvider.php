<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\News;

use App\Entity\News\News;
use App\Entity\News\NewsFile;
use App\FileSystem\Persistence\ContextProviders\AbstractImageFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;
use Symfony\Component\HttpFoundation\File\File;

class NewsFileAdapterContextProvider extends AbstractImageFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return NewsFile::class;
    }

    public function getDirectory(): string
    {
        return 'news';
    }

    public function getFileProperty(): string
    {
        return 'files';
    }

    public function processFile(File $file, array $metadata = [])
    {
        if (isset($metadata['width'], $metadata['height'])) {
            $width = (int) $metadata['width'];
            $height = (int) $metadata['height'];

            $fileContent = $this->imageManager->resize(
                $file->openFile(),
                $width,
                $height
            );

            $file->openFile('w')->fwrite($fileContent);
        }
    }

    /**
     * @param News $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s.%s',
            Utf8Slugger::uniqueSlugify(mb_substr($subject->getTitle(), 0, 32)),
            'jpg'
        );
    }
}
