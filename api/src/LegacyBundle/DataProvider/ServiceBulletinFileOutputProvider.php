<?php

declare(strict_types=1);

namespace LegacyBundle\DataProvider;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\LegacyBundle\Dto\ServiceBulletinFileOutput;
use LegacyBundle\Entity\ServiceBulletinFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ServiceBulletinFileOutputProvider implements ProviderInterface
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

        /* @var ServiceBulletinFile|null $entity */
        return null !== $entity ? $this->toOutput($entity) : null;
    }

    private function toOutput(ServiceBulletinFile $entity): ServiceBulletinFileOutput
    {
        return new ServiceBulletinFileOutput(
            id: $entity->getId(),
            filePath: $entity->file->filePath,
            description: $entity->description,
            createdAt: $entity->file->createdAt->format(\DateTimeInterface::RFC3339),
            extension: $entity->file->extension,
            size: $entity->file->size,
            filename: $entity->filename,
        );
    }
}
