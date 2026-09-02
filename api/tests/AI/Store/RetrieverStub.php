<?php

declare(strict_types=1);

namespace App\Tests\AI\Store;

use Symfony\AI\Platform\Vector\NullVector;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\RetrieverInterface;

final readonly class RetrieverStub implements RetrieverInterface
{
    /**
     * @param array<array<string, mixed>> $metadataList
     */
    public function __construct(private array $metadataList = [])
    {
    }

    public function retrieve(string $query, array $options = []): iterable
    {
        foreach ($this->metadataList as $i => $metadata) {
            yield (new VectorDocument(
                id: (string) $i,
                vector: new NullVector(),
                metadata: new Metadata(['meta' => $metadata]),
            ))->withScore(1.0);
        }
    }
}
