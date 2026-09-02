<?php

declare(strict_types=1);

namespace App\Link\QueryBuilder;

use App\Link\Resource\LinkResourceInterface;
use App\Link\SourceProvider\SourceProvider;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GraphQLQueryBuilder
{
    public function __construct(
        private readonly SourceProvider $sourceProvider,
    ) {
    }

    public function getListQuery(string $class): string
    {
        $reflectionClass = new \ReflectionClass($class);
        if (!$reflectionClass->implementsInterface(LinkResourceInterface::class)) {
            throw new UnprocessableEntityHttpException('Object should be an instance of LinkResourceInterface.');
        }

        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($class);

        return \sprintf(
            <<<'GRAPHQL'
                {
                  %s_findByFilter(params: { startIndex : 0, pageSize :-1 }
                  ) { results {%s}}
                }
                GRAPHQL,
            $resourceSourceProvider->getService(),
            $resourceSourceProvider->getProperties()
        );
    }
}
