<?php

declare(strict_types=1);

namespace App\CQRS\Query\ExtranetUserAcl;

use App\CQRS\Query\QueryInterface;

final class FindAllExtranetUserAclsQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly array $options,
    ) {
    }
}
