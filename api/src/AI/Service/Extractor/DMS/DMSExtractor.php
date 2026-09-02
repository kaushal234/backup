<?php

declare(strict_types=1);

namespace App\AI\Service\Extractor\DMS;

use App\AI\Exception\EntityNotFoundException;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\Extractor\AbstractExtractor;
use App\AI\Service\Extractor\CustomExtractorInterface;
use App\AI\Service\FileTextExtractor;
use App\Entity\DMS;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class DMSExtractor extends AbstractExtractor implements CustomExtractorInterface
{
    /**
     * @param iterable<ModelFactoryInterface> $modelFactories
     */
    public function __construct(
        ManagerRegistry $registry,
        #[AutowireIterator('ai.model_factory')]
        iterable $modelFactories,
        SerializerInterface $serializer,
        EntityAccessCheckerRegistry $accessCheckers,
        private readonly ParameterBagInterface $parameters,
        private readonly FileTextExtractor $fileTextExtractor,
    ) {
        parent::__construct($registry, $modelFactories, $serializer, $accessCheckers);
    }

    public function supports(string $class): bool
    {
        return DMS::class === $class;
    }

    protected function getIdValue(): string
    {
        return 'legacyId';
    }

    /**
     * @param DMS|null $object
     */
    protected function canBeExtracted(?object $object = null): bool
    {
        return null !== $object && null !== $object->getFilepath() && null !== $object->getMimetype();
    }

    /**
     * @param DMS $object
     */
    protected function serialize(string $class, object $object): string
    {
        $filepath = \sprintf('%s/%s', $this->parameters->get('legacy.upload_dir'), $object->getFilepath());
        if (!file_exists($filepath)) {
            throw new EntityNotFoundException('DMS', $object->getId());
        }

        return $this->fileTextExtractor->extract($filepath) ?? '';
    }
}
