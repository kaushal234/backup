<?php

declare(strict_types=1);

namespace AppBundle\Factory\Service;

use ApiBundle\Client;

class CustomerServiceRecordFactory
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function createFromSessionData(array $sessionData): array
    {
        if ([] === $sessionData) {
            return [];
        }

        return [
            'equipmentRecord' => isset($sessionData['equipmentRecord'])
                ? $this->client->get($sessionData['equipmentRecord'])
                : null,
            'airport' => isset($sessionData['airport'])
                ? $this->client->get($sessionData['airport'])
                : null,
            'hourmeter' => $sessionData['hourmeter'] ?? null,
            'title' => $sessionData['title'] ? strip_tags(html_entity_decode($sessionData['title'], \ENT_QUOTES | \ENT_HTML5, 'UTF-8')) : null,
            'description' => $sessionData['description'] ? strip_tags(html_entity_decode($sessionData['description'], \ENT_QUOTES | \ENT_HTML5, 'UTF-8')) : null,
            'tocLegacyId' => $sessionData['tocLegacyId'] ?? null,
            'type' => $sessionData['type'] ?? null,
        ];
    }
}
