<?php

declare(strict_types=1);

namespace LegacyBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringifyExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('stringify', $this->getString(...)),
        ];
    }

    public function getString($item): string
    {
        switch (true) {
            case $item instanceof \DateTime:
                return $item->format(\DateTimeInterface::ATOM);
            case \is_scalar($item):
            case \is_object($item) && method_exists($item, '__toString'):
                return (string) $item;
            case null === $item:
                return '';
        }

        return '-';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'stringify';
    }
}
