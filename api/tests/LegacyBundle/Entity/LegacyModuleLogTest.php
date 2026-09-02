<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Entity;

use ApiPlatform\Metadata\Exception\ResourceClassNotFoundException;
use App\ApiPlatform\UniqueResourceMetadataCollectionFactory;
use App\Doctrine\Mapping\Attributes\Loggable;
use Doctrine\Persistence\ManagerRegistry;
use LegacyBundle\Doctrine\Mapping\Attributes\Synchronize;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LegacyModuleLogTest extends KernelTestCase
{
    private ManagerRegistry $managerRegistry;

    private UniqueResourceMetadataCollectionFactory $resourceMetadataFactory;

    private array $mapping = [];

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->managerRegistry = $managerRegistry;

        /** @var UniqueResourceMetadataCollectionFactory $resourceMetadataFactory */
        $resourceMetadataFactory = static::getContainer()->get(UniqueResourceMetadataCollectionFactory::class);
        $this->resourceMetadataFactory = $resourceMetadataFactory;

        $mapping = static::getContainer()->getParameter('legacy.module_mapping');
        $this->mapping = $mapping;
    }

    public function testEveryLoggableEntitiesAreRegisteredInConfig()
    {
        $metadata = $this->managerRegistry->getManager()->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $metadatum) {
            $loggable = $metadatum->getReflectionClass()->getAttributes(Loggable::class);
            $synchronize = $metadatum->getReflectionClass()->getAttributes(Synchronize::class);
            if (!empty($loggable) && !empty($synchronize)) {
                try {
                    $resourceMetadata = $this->resourceMetadataFactory->getApiResource($metadatum->getName());
                } catch (ResourceClassNotFoundException $resourceClassNotFoundException) {
                    continue;
                }

                $mappingKey = mb_strtolower($resourceMetadata->getShortName());

                self::assertArrayHasKey(
                    $mappingKey,
                    $this->mapping,
                    \sprintf('Class %s must have its legacy acronym registered in config/packages/tld.yaml using the key %s', $metadatum->getName(), $mappingKey)
                );
            }
        }
    }
}
