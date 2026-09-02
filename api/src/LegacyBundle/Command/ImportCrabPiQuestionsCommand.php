<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Quality\Crab;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:crab_pi_questions')]
class ImportCrabPiQuestionsCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly EntityManagerInterface $entityManager;
    private readonly SynchronizationVoter $voter;
    private readonly ActivityLogVoter $activityLogVoter;

    public function __construct(Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, EntityManagerInterface $entityManager, SynchronizationVoter $voter, ActivityLogVoter $activityLogVoter)
    {
        parent::__construct();
        $this->setDescription('Imports CRABS questions from legacy');
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->entityManager = $entityManager;
        $this->voter = $voter;
        $this->activityLogVoter = $activityLogVoter;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import CRABS questions
        $sql = <<<'SQL'
            SELECT pi.id, pi.owner, crab.question_id, crab.parent_id
            FROM pi_questions_unit pi
            INNER JOIN pi_crab_eap crab ON pi.id = crab.question_id
            WHERE crab.question_id > 0 AND pi.owner <>""
            SQL;
        $results = $this->legacyConnection->executeQuery($sql)->fetchAllAssociative();
        $crabCache = $this->cacheFactory->createEntityCache(Crab::class, 'legacyId');
        $pg = new ProgressBar($output, \count($results));

        $this->voter->disable();
        $this->activityLogVoter->disable();
        $i = 0;
        $batchSize = 1000;
        foreach ($results as $result) {
            $pg->advance();

            /** @var Crab|null $crab */
            $crab = $crabCache->fetch((string) $result['parent_id']);

            if (null === $crab) {
                continue;
            }

            if ($result['question_id'] > 0) {
                $crab->piQuestionId = $result['question_id'];
            }

            if ('' !== $result['owner']) {
                $crab->piQuestionType = $result['owner'];
            }

            ++$i;

            if (($i % $batchSize) === 0) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();

        $pg->finish();
        $this->voter->enable();
        $this->activityLogVoter->enable();

        return Command::SUCCESS;
    }
}
