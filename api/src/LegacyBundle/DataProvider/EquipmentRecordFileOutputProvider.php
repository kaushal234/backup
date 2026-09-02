<?php

declare(strict_types=1);

namespace LegacyBundle\DataProvider;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\LegacyBundle\Dto\EquipmentRecordFileOutput;
use LegacyBundle\Entity\EquipmentRecordFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EquipmentRecordFileOutputProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider,

        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ItemProvider $itemProvider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof GetCollection) {
            $collection = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $items = [];
            foreach ($collection as $entity) {
                $items[] = $this->toOutput($entity);
            }

            return $items;
        }

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);

        /* @var EquipmentRecordFile|null $entity */
        return null !== $entity ? $this->toOutput($entity) : null;
    }

    private function toOutput(EquipmentRecordFile $entity): EquipmentRecordFileOutput
    {
        return new EquipmentRecordFileOutput(
            id: $entity->getId(),
            parentId: $entity->parentId,
            description: $entity->description,
            date: $entity->date?->format('Y-m-d'),
            filename: $entity->filename,
            // The legacy stores files as "{timestamp}-{originalName}"; strip the technical prefix for display.
            displayFilename: preg_replace('/^\d+-/', '', $entity->filename) ?? $entity->filename,
        );
    }
}
