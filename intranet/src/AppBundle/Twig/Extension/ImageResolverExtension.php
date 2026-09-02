<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageResolverExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('resolve_image_url', [$this, 'resolveImageUrl']),
        ];
    }

    public function resolveImageUrl($value, $index = null)
    {
        if (\is_callable($value)) {
            return $value($index);
        }

        return $value;
    }
}
