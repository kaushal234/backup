<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\SanitationHelper;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:esr:fix_data')]
class ImportEquipmentShippingRecordArrivalPlaceCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly EntityManagerInterface $entityManager;
    private readonly SynchronizationVoter $voter;
    private readonly ActivityLogVoter $activityLogVoter;
    private SanitationHelper $sanitationHelper;

    public function __construct(Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, EntityManagerInterface $entityManager, SynchronizationVoter $voter, ActivityLogVoter $activityLogVoter, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Imports ESR arrival_place from legacy');
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->entityManager = $entityManager;
        $this->voter = $voter;
        $this->activityLogVoter = $activityLogVoter;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import ESR arrival
        $sql = <<<'SQL'
            SELECT id, arrival
            FROM esr
            SQL;
        $results = $this->legacyConnection->executeQuery($sql)->fetchAllAssociative();
        $esrCache = $this->cacheFactory->createEntityCache(EquipmentShippingRecord::class, 'legacyId');
        $pg = new ProgressBar($output, \count($results));

        $this->voter->disable();
        $this->activityLogVoter->disable();
        foreach ($results as $result) {
            $pg->advance();

            /** @var EquipmentShippingRecord|null $esr */
            $esr = $esrCache->fetch((string) $result['id']);
            if (!$esr instanceof EquipmentShippingRecord) {
                continue;
            }

            $esr->arrivalPlace = $this->sanitationHelper->trimAndNullify($result['arrival']);
            $this->entityManager->persist($esr);
        }

        $this->entityManager->flush();

        $pg->finish();
        $this->voter->enable();
        $this->activityLogVoter->enable();

        return Command::SUCCESS;
    }
}
