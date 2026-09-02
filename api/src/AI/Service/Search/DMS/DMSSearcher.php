<?php

declare(strict_types=1);

namespace App\AI\Service\Search\DMS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\DmsSearch;
use App\AI\Dto\Result;
use App\AI\Factory\AILogFactory;
use App\AI\Service\Search\AbstractSearcher;
use App\Entity\DMS;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DMSSearcher extends AbstractSearcher
{
    public function __construct(
        #[Autowire(service: 'ai.retriever.dms')]
        protected RetrieverInterface $retriever,
        protected AILogFactory $factory,
        protected IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($retriever, $factory, $iriConverter);
    }

    protected function getDtoClass(): string
    {
        return DmsSearch::class;
    }

    protected function getUniqueId(array $metadata): ?string
    {
        return isset($metadata['dms_id']) ? (string) $metadata['dms_id'] : null;
    }

    protected function processResults(array $results): Collection
    {
        $dmsRepository = $this->entityManager->getRepository(DMS::class);
        $data = new ArrayCollection();
        foreach ($results as $metadata) {
            $dmsId = $metadata['dms_id'];
            $dms = $dmsRepository->findOneBy(['legacyId' => $dmsId]);
            $description = 'Confidential';
            if ($this->security->isGranted('DMS_PEOPLE_VIEW_VOTER', $dms)) {
                $description = $dms->getTitle();
            }

            $result = new Result(
                id: $dmsId,
                module: 'DMS',
                description: $description,
                link: $this->urlGenerator->generate('dms', ['m' => ['view'], 'id' => $dmsId]),
            );
            $data->add($result);
        }

        return $data;
    }

    protected function getOperation(): string
    {
        return '/search/dms';
    }
}
