<?php

declare(strict_types=1);

namespace App\CQRS\Query;

use App\Sdk\Resource\Representative;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<int, Representative>>
 */
final class FindAllRepresentativeQuery implements QueryInterface
{
}
