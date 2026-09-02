<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Sales\AircraftCompatibility\Aircraft;
use App\Entity\Sales\AircraftCompatibility\AircraftCompatibility;
use App\Entity\Sales\Product;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:aircraft_compatibility')]
class ImportAircraftCompatibilityCommand extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly SynchronizationVoter $synchronizationVoter,
        private readonly ActivityLogVoter $activityLogVoter,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Imports Aircraft Compatibilities from legacy');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productCache = $this->cacheFactory->createEntityCache(Product::class, 'name');
        $aircraftCache = $this->cacheFactory->createEntityCache(Aircraft::class, 'name');

        $this->synchronizationVoter->disable();
        $this->activityLogVoter->disable();

        // Import aircraft compatibilities
        $sql = <<<'SQL'
            SELECT parent_id, model FROM nto_models
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        foreach ($stmt->fetchAllAssociative() as $ntoModel) {
            $aircraftCompatibility = $this->getAircraftCompatibility((int) $ntoModel['parent_id']);

            /** @var Product|null $product */
            $product = $productCache->fetch($ntoModel['model']);
            if (null !== $product) {
                $aircraftCompatibility->addProduct($product);
            }

            $this->entityManager->persist($aircraftCompatibility);
            $this->entityManager->flush();
        }

        $sql = <<<'SQL'
            SELECT parent_id, aircraft_model FROM nto_aircraft
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        foreach ($stmt->fetchAllAssociative() as $ntoAircraft) {
            $aircraftCompatibility = $this->getAircraftCompatibility((int) $ntoAircraft['parent_id']);

            /** @var Aircraft|null $aircraft */
            $aircraft = $aircraftCache->fetch($ntoAircraft['aircraft_model']);
            if (null !== $aircraft) {
                $aircraftCompatibility->addAircraft($aircraft);
            }

            $this->entityManager->persist($aircraftCompatibility);
            $this->entityManager->flush();
        }

        $aircraftCompatibilityRepository = $this->entityManager->getRepository(AircraftCompatibility::class);
        foreach ($aircraftCompatibilityRepository->findAll() as $existingAircraftCompatibility) {
            if ($existingAircraftCompatibility->getAircrafts()->isEmpty() && $existingAircraftCompatibility->getProducts()->isEmpty()) {
                $this->entityManager->remove($existingAircraftCompatibility);
            }
        }

        $this->synchronizationVoter->enable();
        $this->activityLogVoter->enable();

        return Command::SUCCESS;
    }

    private function getAircraftCompatibility(int $legacyId): AircraftCompatibility
    {
        $aircraftCompatibilityRepository = $this->entityManager->getRepository(AircraftCompatibility::class);

        /** @var AircraftCompatibility|null $aircraftCompatibility */
        $aircraftCompatibility = $aircraftCompatibilityRepository->findOneBy(['legacyId' => $legacyId]);
        if (null === $aircraftCompatibility) {
            $aircraftCompatibility = new AircraftCompatibility();
            $aircraftCompatibility->legacyId = $legacyId;
        }

        return $aircraftCompatibility;
    }
}
