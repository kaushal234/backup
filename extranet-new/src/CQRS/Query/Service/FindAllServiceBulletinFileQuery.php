<?php

declare(strict_types=1);

namespace App\CQRS\Query\Service;

use App\CQRS\Query\QueryInterface;

final class FindAllServiceBulletinFileQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
