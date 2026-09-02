<?php

declare(strict_types=1);

namespace App\AI\DataProvider\Search;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\AI\Exception\NoResultException;
use App\AI\Service\Search\SearcherInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class SearchDataProvider implements ProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        #[AutowireIterator(tag: 'ai.searcher')]
        private iterable $searchers,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $query = $this->requestStack->getCurrentRequest()->query->get('query');

        if (null === $query) {
            throw new BadRequestHttpException('Filter query is mandatory on this route.');
        }

        /** @var SearcherInterface $searcher */
        foreach ($this->searchers as $searcher) {
            if (!$searcher->supports($operation->getClass())) {
                continue;
            }

            try {
                return $searcher->search(query: $query, createLog: true);
            } catch (NoResultException $exception) {
                // do nothing, it will return null at the end
            }
        }

        return null;
    }
}
