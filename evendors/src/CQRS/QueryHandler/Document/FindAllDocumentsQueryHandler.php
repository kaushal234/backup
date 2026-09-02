<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Document;

use App\CQRS\Query\Document\FindAllDocumentsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Document;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllDocumentsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, Document>
     */
    public function __invoke(FindAllDocumentsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Document::class);
    }
}
