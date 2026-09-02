<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use Doctrine\ORM\PersistentCollection;

interface CollectionHandlerInterface
{
    public function handleUpdates(PersistentCollection $collection);

    public function handleDeletions(PersistentCollection $collection);

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool;
}
