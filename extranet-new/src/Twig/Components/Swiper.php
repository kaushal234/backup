<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Sdk\Resource\File;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Swiper
{
    /** @var File[] */
    public iterable $files = [];

    public ?\Closure $fileRouteBuilder = null;

    /**
     * @return array<array{data: File, route: string|null}>
     */
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
