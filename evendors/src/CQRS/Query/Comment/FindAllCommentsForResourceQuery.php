<?php

declare(strict_types=1);

namespace App\CQRS\Query\Comment;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\ResourceInterface;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<int, Comment>>
 */
final class FindAllCommentsForResourceQuery implements QueryInterface
{
    public function __construct(
        public readonly ResourceInterface $resource,
    ) {
    }
}
