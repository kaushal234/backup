<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use AppBundle\Manager\FileExtensionManager;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

class FileExtension extends AbstractExtension
{
    public const VIEWABLE_FILE = ['png', 'jpg', 'jpeg'];

    public function __construct(
        private readonly FileExtensionManager $fileExtensionManager,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('is_viewable', [$this->fileExtensionManager, 'isViewable']),
            new TwigFilter('file_name', $this->fileName(...)),
            new TwigFilter('fa_icon_class', $this->getBootstrapSvg(...)),
        ];
    }

    public function fileName(array $file): string
    {
        $basename = pathinfo($file['filePath'], \PATHINFO_BASENAME);

        return mb_substr($basename, 23);
    }

    public function getBootstrapSvg(array $file): Markup
    {
        $svg = $this->fileExtensionManager->getBootstrapSvg($file);

        return new Markup($svg, 'UTF-8');
    }
}
