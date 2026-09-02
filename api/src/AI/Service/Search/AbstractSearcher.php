<?php

declare(strict_types=1);

namespace App\AI\Service\Search;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\Result;
use App\AI\Dto\SearchOutput;
use App\AI\Exception\NoResultException;
use App\AI\Factory\AILogFactory;
use Doctrine\Common\Collections\Collection;
use Symfony\AI\Store\RetrieverInterface;

#[FeatureDoc(path: 'ai-searcher.md')]
abstract class AbstractSearcher implements SearcherInterface
{
    public function __construct(
        protected RetrieverInterface $retriever,
        protected AILogFactory $factory,
        protected IriConverterInterface $iriConverter,
    ) {
    }

    public function search(string $query, int $limit = 10, bool $createLog = false): SearchOutput
    {
        $documents = $this->retriever->retrieve($query, ['limit' => $limit * 20]);

        $bestById = [];
        foreach ($documents as $document) {
            $metadata = iterator_to_array($document->getMetadata())['meta'];

            $uniqueId = $this->getUniqueId($metadata);
            if (null === $uniqueId) {
                continue;
            }

            if (!isset($bestById[$uniqueId]) || $document->getScore() > $bestById[$uniqueId]['score']) {
                $bestById[$uniqueId] = [
                    'score' => $document->getScore(),
                    'metadata' => $metadata,
                ];
            }
        }

        $metadataResults = array_values(array_map(
            static fn (array $item) => $item['metadata'],
            \array_slice($bestById, 0, $limit),
        ));

        $response = $this->processResults($metadataResults)->toArray();

        if (0 === \count($response)) {
            throw new NoResultException();
        }

        $logIri = null;
        if ($createLog) {
            $request = $this->factory->createRequest($this->getOperation(), [], null, $query);
            $log = $this->factory->createLog($request, json_encode($response, \JSON_THROW_ON_ERROR));
            $logIri = $this->iriConverter->getIriFromResource($log);
        }

        $output = new SearchOutput();
        $output->results = $response;
        $output->logIri = $logIri;

        return $output;
    }

    public function supports(string $class): bool
    {
        return $class === $this->getDtoClass();
    }

    abstract protected function getDtoClass(): string;

    /**
     * Returns the unique identifier for a document based on its metadata, used for deduplication.
     * Return null to skip the document.
     */
    abstract protected function getUniqueId(array $metadata): ?string;

    /**
     * @return Collection<Result>
     */
    abstract protected function processResults(array $results): Collection;

    abstract protected function getOperation(): string;
}
