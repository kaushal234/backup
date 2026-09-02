<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\FileSystem\Image\ImageManager;
use App\FileSystem\TemporaryStorageManager;
use Symfony\Component\HttpFoundation\File\File;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    private readonly ImageManager $imageManager;
    private readonly TemporaryStorageManager $temporaryStorageManager;

    public function __construct(ImageManager $imageManager, TemporaryStorageManager $temporaryStorageManager)
    {
        $this->imageManager = $imageManager;
        $this->temporaryStorageManager = $temporaryStorageManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isLandscape', $this->isLandscape(...), ['is_safe' => ['all']]),
            new TwigFunction('rotate', $this->rotate(...), ['is_safe' => ['all']]),
        ];
    }

    public function isLandscape($path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        [$width, $height] = getimagesize($path);

        return $width > $height;
    }

    public function rotate($path): ?string
    {
        if (!$this->isLandscape($path)) {
            return $path;
        }

        $file = new File($path);
        $imageContent = $this->imageManager->rotate($file->openFile(), 90);

        return $this->temporaryStorageManager->createTemporaryFileFromContent($imageContent, 'jpg');
    }
}
