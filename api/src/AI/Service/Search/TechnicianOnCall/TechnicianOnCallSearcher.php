<?php

declare(strict_types=1);

namespace App\AI\Service\Search\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\TechnicianOnCallSearch;
use App\AI\Factory\AILogFactory;
use App\AI\Handler\LegacySearchSourceHandler;
use App\AI\Handler\SearchSourceHandlerInterface;
use App\AI\Service\Search\AbstractSearcher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class TechnicianOnCallSearcher extends AbstractSearcher
{
    public function __construct(
        #[Autowire(service: 'ai.retriever.toc')]
        protected RetrieverInterface $retriever,
        protected AILogFactory $factory,
        protected IriConverterInterface $iriConverter,
        #[Autowire(service: LegacySearchSourceHandler::class)]
        private readonly SearchSourceHandlerInterface $handler,
    ) {
        parent::__construct($retriever, $factory, $iriConverter);
    }

    protected function getDtoClass(): string
    {
        return TechnicianOnCallSearch::class;
    }

    protected function getUniqueId(array $metadata): ?string
    {
        return isset($metadata['tocid']) ? (string) $metadata['tocid'] : null;
    }

    protected function processResults(array $results): Collection
    {
        $data = new ArrayCollection();
        foreach ($results as $metadata) {
            $item = $this->handler->handle(['id' => $metadata['tocid'], 'src' => 'toc']);

            if (null !== $item) {
                $data->add($item);
            }
        }

        return $data;
    }

    protected function getOperation(): string
    {
        return '/search/toc';
    }
}
