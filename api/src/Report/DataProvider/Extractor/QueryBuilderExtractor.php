<?php

declare(strict_types=1);

namespace App\Report\DataProvider\Extractor;

use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class QueryBuilderExtractor
{
    private readonly QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function __invoke(): array
    {
        try {
            return $this->queryBuilder->getQuery()->getScalarResult();
        } catch (QueryException $queryException) {
            throw new BadRequestHttpException('Something went wrong with the submitted parameters', $queryException);
        }
    }
}
