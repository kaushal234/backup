<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Iri\Iri;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ApiExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('iri_id', [$this, 'getId']),
            new TwigFilter('iri_type', [$this, 'getType']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('iri_id', [$this, 'getId']),
            new TwigFunction('iri_type', [$this, 'getType']),
        ];
    }

    public function getId($item)
    {
        return Iri::id($item);
    }

    public function getType($item)
    {
        return Iri::type($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'api';
    }
}
