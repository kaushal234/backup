<?php

declare(strict_types=1);

namespace App\CQRS\Query\News;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\EvendorsNews;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<EvendorsNews>>
 */
class FindCurrentEvendorsNewsQuery implements QueryInterface
{
}
