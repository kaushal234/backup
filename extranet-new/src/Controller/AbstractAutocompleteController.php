<?php

declare(strict_types=1);

namespace App\Controller;

use App\CQRS\Query\QueryInterface;
use App\CQRS\QueryBusInterface;
use App\Sdk\Page;
use App\Sdk\Utils\IriToId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractAutocompleteController
{
    protected QueryBusInterface $queryBus;
    protected RouterInterface $router;

    public function __invoke(Request $request): Response
    {
        $term = (string) $request->query->get('query', '');
        $page = $request->query->getInt('page', 1);

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'formatter' => static fn ($item) => $item->name,
            'orderProperty' => 'name',
            'orderDirection' => 'ASC',
            'searchKey' => 'q',
            'queryOptions' => null,
        ]);

        $resolver->setRequired(['routeName', 'queryBus']);

        $resolver
            ->setAllowedTypes('routeName', 'string')
            ->setAllowedTypes('formatter', 'closure')
            ->setAllowedTypes('orderProperty', 'string')
            ->setAllowedTypes('orderDirection', 'string')
            ->setAllowedTypes('searchKey', 'string')
            ->setAllowedTypes('queryBus', 'closure')
        ;

        $resolver->setAllowedValues('orderDirection', ['ASC', 'DESC']);

        $this->configureOptions($resolver, $request);

        /** * @var array{
         * routeName: string,
         * queryBus: \Closure(int, array<string, mixed>): QueryInterface,
         * formatter: \Closure(object): string,
         * orderProperty: string,
         * orderDirection: 'ASC'|'DESC',
         * searchKey: string,
         * queryOptions: array<string, mixed>|null
         * } $options
         */
        $options = $resolver->resolve();

        /** @var array<string, mixed> $queryOptions */
        $queryOptions = ['order' => [$options['orderProperty'] => $options['orderDirection']]];

        if (mb_strlen($term) > 2) {
            $queryOptions[$options['searchKey']] = $term;
        }

        if (null !== $options['queryOptions']) {
            $queryOptions = array_merge($queryOptions, $options['queryOptions']);
        }

        /** @var QueryInterface $query */
        $query = $options['queryBus']($page, $queryOptions);

        /** @var Page $results */
        $results = $this->queryBus->dispatch($query);

        $formatter = $this->formatItem(...);

        return new JsonResponse([
            'results' => array_map(
                static fn ($item) => $formatter($options, $item),
                $results->items->toArray()
            ),
            'next_page' => $results->hasNext
                ? $this->router->generate($options['routeName'], ['query' => $term, 'page' => $page + 1])
                : null,
        ]);
    }

    #[Required]
    public function setQueryBus(QueryBusInterface $queryBus): void
    {
        $this->queryBus = $queryBus;
    }

    #[Required]
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    abstract protected function configureOptions(OptionsResolver $resolver, Request $request): void;

    /**
     * @param array<string, mixed> $options
     *
     * @return array{value: string|int, text: string}
     */
    protected function formatItem(array $options, object $item): array
    {
        return [
            'value' => IriToId::iriToId($item->iri),
            'text' => $options['formatter']($item),
        ];
    }
}
