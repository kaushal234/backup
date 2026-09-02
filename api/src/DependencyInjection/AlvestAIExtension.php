<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AlvestAIExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $entities = $config['entities'] ?? [];
        $apiSearch = [];
        $legacySearch = [];

        foreach ($entities as $slug => $entity) {
            if (isset($entity['search'])) {
                $apiSearch[$entity['search']['src']] = [
                    'class' => $entity['class'],
                    'fields' => $entity['search']['fields'],
                    'route' => $entity['search']['route'],
                ];
            }

            if (isset($entity['legacy_search'])) {
                $legacy = $entity['legacy_search'];
                $src = $legacy['src'];
                $legacySearch[$src] = [
                    'fields' => $legacy['fields'],
                    'table' => $legacy['table'] ?? $src,
                    'route' => $legacy['route'],
                    'route_parameter' => $legacy['route_parameter'] ?? $src,
                    'route_with_no_params' => $legacy['route_with_no_params'],
                ];
            }
        }

        $container->setParameter('alvest_ai.entities', $entities);
        $container->setParameter('alvest_ai.search.api', $apiSearch);
        $container->setParameter('alvest_ai.search.legacy', $legacySearch);
    }
}
