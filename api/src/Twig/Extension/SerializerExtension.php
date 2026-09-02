<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SerializerExtension extends AbstractExtension
{
    private readonly SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('serialize_symfony', $this->serialize(...)),
        ];
    }

    public function serialize($data, string $type = 'json', array $context = []): string
    {
        return $this->serializer->serialize($data, $type, $context);
    }
}
