<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class JpegExistsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('jpeg_exists', $this->fileExistsAndIsJpeg(...), ['needs_environment' => true, 'is_safe' => ['all']]),
        ];
    }

    public function fileExistsAndIsJpeg(Environment $env, $path): bool
    {
        try {
            $file = new File($path);
        } catch (FileNotFoundException $e) {
            return false;
        }

        return 'image/jpeg' === $file->getMimeType();
    }
}
