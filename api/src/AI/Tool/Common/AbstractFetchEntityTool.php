<?php

declare(strict_types=1);

namespace App\AI\Tool\Common;

use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\AIEntityRegistry;
use App\AI\Tool\ToolInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;

abstract readonly class AbstractFetchEntityTool implements ToolInterface
{
    public function __construct(
        private ManagerRegistry $registry,
        private SerializerInterface $serializer,
        private EntityAccessCheckerRegistry $accessCheckers,
        protected AIEntityRegistry $entities,
    ) {
    }

    public function __invoke(string $entityType, int $id): string
    {
        $class = $this->entities->getEntityClass($entityType);

        $entityManager = $this->registry->getManagerForClass($class);
        if (null === $entityManager) {
            throw new \LogicException(\sprintf('No entity manager found for class "%s".', $class));
        }

        $entity = $entityManager->getRepository($class)->findOneBy(['id' => $id]);
        if (null === $entity) {
            throw new EntityNotFoundException($this->getShortName($class), $id);
        }

        if (!$this->accessCheckers->isGranted($class, $entity)) {
            throw new AccessDeniedException();
        }

        return $this->serializer->serialize($this->loadData($entityType, $entity), 'json');
    }

    /**
     * @return object[]
     */
    abstract protected function loadData(string $entityType, object $entity): array;

    private function getShortName(string $class): string
    {
        return mb_substr(mb_strrchr($class, '\\'), 1);
    }
}
