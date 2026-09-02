<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\Country;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallZone;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:service:technician_on_call:zone')]
class ImportTechnicianOnCallZoneCommand extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityCacheHelperFactory $cacheFactory,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
                SELECT *
                FROM toc_zones;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $zoneRepository = $this->entityManager->getRepository(TechnicianOnCallZone::class);
        $zones = $zoneRepository->findAll();

        foreach ($stmt->fetchAllAssociative() as $legacyZone) {
            $countryCache = $this->cacheFactory->createEntityCache(Country::class, 'legacyId');
            $country = $countryCache->fetch((string) $legacyZone['parent_id']);

            if (!$country) {
                $output->writeln(\sprintf('<error>Country with id %s not found</error>', $legacyZone['parent_id']));
                continue;
            }

            $filteredZones = array_filter($zones, static fn (TechnicianOnCallZone $zone) => $zone->name === $legacyZone['zone']);
            /** @var TechnicianOnCallZone $zone */
            $zone = reset($filteredZones);
            $zone->addCountry($country);
            $this->entityManager->persist($zone);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
