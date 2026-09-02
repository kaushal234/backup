<?php

declare(strict_types=1);

namespace App\Http\Link;

use App\Link\QueryBuilder\GraphQLMutationBuilder;
use App\Link\QueryBuilder\GraphQLQueryBuilder;
use App\Link\SourceProvider\SourceProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LinkClient extends AbstractLinkClient implements LinkClientInterface
{
    public function __construct(
        protected SourceProvider $sourceProvider,
        private readonly GraphQLMutationBuilder $mutationBuilder,
        private readonly GraphQLQueryBuilder $queryBuilder,
        private readonly HttpClientInterface $linkClient,
        private readonly bool $httpCallEnabled = true,
    ) {
        parent::__construct($sourceProvider);
    }

    public function getCollection(string $class, string $key = 'name', array $variables = []): array
    {
        if (!$this->httpCallEnabled) {
            return [];
        }
        $results = $this->query($this->linkClient, $this->queryBuilder->getListQuery($class), $variables);
        $function = $this->getFunction($class);

        return array_combine(array_column($results['data'][$function]['results'], $key), $results['data'][$function]['results']);
    }

    public function mutate(object $object, array $groups = [], array $extraProperties = [], array $variables = []): void
    {
        if (!$this->httpCallEnabled) {
            return;
        }

        $this->query($this->linkClient, $this->mutationBuilder->getMutation($object, $groups, $extraProperties), $variables);
    }

    public function archive(object $object, array $variables = []): array
    {
        if (!$this->httpCallEnabled) {
            return [];
        }
        $results = $this->query($this->linkClient, $this->mutationBuilder->getArchiveMutation($object), $variables);

        return $results['data'][$this->getFunction($this->getRealClassName($object), 'mutate')];
    }
}
