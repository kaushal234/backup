<?php

declare(strict_types=1);

namespace App\AI\Service\Extractor;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Serializer\SerializerInterface;

#[FeatureDoc(path: 'ai-extractor.md')]
class GenericExtractor extends AbstractExtractor
{
    /**
     * @param iterable<ModelFactoryInterface>    $modelFactories
     * @param iterable<CustomExtractorInterface> $customExtractors
     */
    public function __construct(
        ManagerRegistry $registry,
        #[AutowireIterator('ai.model_factory')]
        iterable $modelFactories,
        SerializerInterface $serializer,
        EntityAccessCheckerRegistry $accessCheckers,
        #[AutowireIterator('ai.extractor.custom')]
        private readonly iterable $customExtractors,
    ) {
        parent::__construct($registry, $modelFactories, $serializer, $accessCheckers);
    }

    public function extract(string $class, array $uriVariables): string
    {
        foreach ($this->customExtractors as $custom) {
            if ($custom->supports($class)) {
                return $custom->extract($class, $uriVariables);
            }
        }

        return parent::extract($class, $uriVariables);
    }
}
