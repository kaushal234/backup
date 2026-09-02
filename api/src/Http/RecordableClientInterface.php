<?php

declare(strict_types=1);

namespace App\Http;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[FeatureDoc(path: 'recordable-client.md')]
interface RecordableClientInterface
{
    public function getFixturesDirectory(): string;

    public function getUrl(string $operation, string|int|null $id): string;

    public function getQueryParameters(array $options): array;

    public function request(string $method, string $url, array $options = []): ResponseInterface;
}
