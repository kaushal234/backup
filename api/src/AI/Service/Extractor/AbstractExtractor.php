<?php

declare(strict_types=1);

namespace App\AI\Service\Extractor;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Serializer\SerializerInterface;

#[FeatureDoc(path: 'ai-extractor.md')]
abstract class AbstractExtractor implements ExtractorInterface
{
    /**
     * @param iterable<ModelFactoryInterface> $modelFactories
     */
    public function __construct(
        private readonly ManagerRegistry $registry,
        #[AutowireIterator('ai.model_factory')]
        private readonly iterable $modelFactories,
        private readonly SerializerInterface $serializer,
        private readonly EntityAccessCheckerRegistry $accessCheckers,
    ) {
    }

    public function extract(string $class, array $uriVariables): string
    {
        $entityManager = $this->registry->getManagerForClass($class);
        $object = $entityManager->getRepository($class)->findOneBy($uriVariables);

        if (!$this->canBeExtracted($object)) {
            throw new EntityNotFoundException($this->getShortName($class), $uriVariables[$this->getIdValue()]);
        }

        if (!$this->accessCheckers->isGranted($class, $object)) {
            throw new AccessDeniedException();
        }

        return $this->serialize($class, $object);
    }

    protected function getIdValue(): string
    {
        return 'id';
    }

    protected function canBeExtracted(?object $object = null): bool
    {
        return null !== $object;
    }

    protected function getShortName(string $class): string
    {
        $shortName = mb_strrchr($class, '\\');

        return false === $shortName ? $class : mb_substr($shortName, 1);
    }

    protected function serialize(string $class, object $object): string
    {
        return $this->serializer->serialize($this->findFactory($class)->create($object), 'json');
    }

    private function findFactory(string $class): ModelFactoryInterface
    {
        foreach ($this->modelFactories as $factory) {
            if ($factory->supports($class)) {
                return $factory;
            }
        }

        throw new \LogicException(\sprintf('No model factory found supporting class "%s".', $class));
    }
}
