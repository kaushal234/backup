<?php

declare(strict_types=1);

namespace App\FileSystem\Image;

use Intervention\Image\ImageManager as InterventionManager;

class ImageManager
{
    /**
     * @var int
     */
    final public const DEFAULT_WIDTH = 1_280;

    private readonly InterventionManager $interventionManager;

    public function __construct(InterventionManager $interventionManager)
    {
        $this->interventionManager = $interventionManager;
    }

    public function resize(\SplFileObject $fileObject, ?int $width = null, ?int $height = null)
    {
        $image = $this->interventionManager->decodeSplFileInfo($fileObject);

        if (null === $width && null === $height) {
            $width = self::DEFAULT_WIDTH;
        }

        switch (true) {
            // full resize : fit image in new size and fill with white
            case null !== $width && null !== $height:
                $ratio = min(
                    $width / $image->width(),
                    $height / $image->height()
                );

                $sampledWidth = $ratio * $image->width();
                $sampledHeight = $ratio * $image->height();

                $image = $image->resize((int) $sampledWidth, (int) $sampledHeight);

                $image = $image->resizeCanvas($width, $height);
                break;
                // resize to targeted height
            case null !== $height:
                $image = $image->resize(height: $height);
                break;
                // resize to targeted width
            case null !== $width:
                $image = $image->resize(width: $width);
                break;
        }

        return $image->encode()->toString();
    }

    public function rotate(\SplFileObject $fileObject, int $rotation = 0)
    {
        $image = $this->interventionManager->decodeSplFileInfo($fileObject);
        $image->rotate($rotation);

        return $image->encode()->toString();
    }
}
