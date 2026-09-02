<?php

declare(strict_types=1);

namespace App\Twig;

use App\Sdk\Utils\IriToId;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IriIdExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('iri_id', [$this, 'getId']),
        ];
    }

    public function getId(string $item): int
    {
        return IriToId::iriToId($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'iri_to_id';
    }
}
