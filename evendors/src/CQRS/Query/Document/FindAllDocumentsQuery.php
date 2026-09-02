<?php

declare(strict_types=1);

namespace App\CQRS\Query\Document;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Document;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<int, Document>>
 */
final class FindAllDocumentsQuery implements QueryInterface
{
}
