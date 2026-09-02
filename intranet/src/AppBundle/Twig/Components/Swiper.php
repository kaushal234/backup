<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Swiper
{
    public iterable $files = [];

    public ?string $uploadUri = null;

    public $fileRouteBuilder;

    public function getProcessedFiles(): array
    {
        $processed = [];

        foreach ($this->files as $file) {
            $route = null;

            if (\is_callable($this->fileRouteBuilder)) {
                $route = \call_user_func($this->fileRouteBuilder, $file);
            }

            $processed[] = [
                'data' => $file,
                'route' => $route,
            ];
        }

        return $processed;
    }
}
