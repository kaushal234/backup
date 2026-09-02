<?php

declare(strict_types=1);

namespace App\Manager\Transfer;

use App\Doctrine\EntityOwnerFinder;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Manager\Transfer\Handler\TransferHandlerInterface;
use Doctrine\Common\Util\ClassUtils;

class EntityTransferManager
{
    /**
     * @var TransferHandlerInterface[]
     */
    private array $handlers = [];
    private readonly EntityOwnerFinder $entityRelationsFinder;
    private readonly string $alias;

    /**
     * OwnershipTransferManager constructor.
     *
     * @param string $alias
     */
    public function __construct(EntityOwnerFinder $entityRelationsFinder, $alias = 'manager.generic')
    {
        $this->entityRelationsFinder = $entityRelationsFinder;
        $this->alias = $alias;
    }

    public function transfer(?object $source, object $target, $conditions = []): void
    {
        $sourceClass = null !== $source ? ClassUtils::getRealClass($source::class) : null;
        $targetClass = ClassUtils::getRealClass($target::class);

        if (null !== $sourceClass && $sourceClass !== $targetClass) {
            throw new \InvalidArgumentException(\sprintf('Both objects have to be of the same type (got %s, %s)', $sourceClass, $targetClass));
        }

        $relations = null !== $sourceClass ? $this->entityRelationsFinder->getOwners($sourceClass) : $this->entityRelationsFinder->getOwners($targetClass);
        foreach ($relations as $relationMetadataBag) {
            $propertyAttributes = $relationMetadataBag->getReflectionProperty()->getAttributes(Transferable::class);
            foreach ($propertyAttributes as $propertyAttribute) {
                $transferable = $propertyAttribute->newInstance();
                if ($transferable->manager !== $this->alias) {
                    continue;
                }

                if (!\array_key_exists($transferable->handler, $this->handlers)) {
                    continue;
                }
                $handler = $this->handlers[$transferable->handler];

                $handler->handle($source, $target, $relationMetadataBag, array_merge($transferable->conditions, $conditions));
            }
        }
    }

    public function registerHandler(TransferHandlerInterface $handler)
    {
        $this->handlers[$handler->getName()] = $handler;
    }
}
