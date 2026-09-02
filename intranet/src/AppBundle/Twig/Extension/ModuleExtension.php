<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleExtension extends AbstractExtension
{
    public function __construct(private readonly Client $client)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('module', [$this, 'getModule']),
        ];
    }

    public function getModule(string $name): ?ApiData
    {
        try {
            return $this->client->findOneBy('modules', ['exact' => ['name' => $name]], ['cache' => true]);
        } catch (\Exception $e) {
            return null;
        }
    }
}
