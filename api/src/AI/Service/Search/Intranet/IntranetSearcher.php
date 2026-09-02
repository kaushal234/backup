<?php

declare(strict_types=1);

namespace App\AI\Service\Search\Intranet;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\IntranetSearch;
use App\AI\Factory\AILogFactory;
use App\AI\Service\Search\AbstractSearcher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class IntranetSearcher extends AbstractSearcher
{
    public function __construct(
        #[Autowire(service: 'ai.retriever.intranet')]
        protected RetrieverInterface $retriever,
        protected AILogFactory $factory,
        protected IriConverterInterface $iriConverter,
        #[AutowireIterator('search.handler')]
        private readonly iterable $handlers,
    ) {
        parent::__construct($retriever, $factory, $iriConverter);
    }

    protected function getDtoClass(): string
    {
        return IntranetSearch::class;
    }

    protected function getUniqueId(array $metadata): ?string
    {
        if (!isset($metadata['src'], $metadata['id'])) {
            return null;
        }

        return \sprintf('%s_%s', $metadata['src'], $metadata['id']);
    }

    protected function processResults(array $results): Collection
    {
        $data = new ArrayCollection();
        foreach ($results as $result) {
            $module = mb_strtolower($result['src']);

            foreach ($this->handlers as $handler) {
                if (!$handler->supports($module)) {
                    continue;
                }

                $item = $handler->handle($result);
                if (null !== $item) {
                    $data->add($item);
                }

                break;
            }
        }

        return $data;
    }

    protected function getOperation(): string
    {
        return '/search/intranet';
    }
}
