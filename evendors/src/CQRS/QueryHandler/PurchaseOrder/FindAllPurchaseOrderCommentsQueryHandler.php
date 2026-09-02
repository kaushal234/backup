<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\PurchaseOrder;

use App\CQRS\Query\PurchaseOrder\FindAllPurchaseOrderCommentsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Comment;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllPurchaseOrderCommentsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, Comment>
     */
    public function __invoke(FindAllPurchaseOrderCommentsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Comment::class, ['resource' => $query->resource]);
    }
}
