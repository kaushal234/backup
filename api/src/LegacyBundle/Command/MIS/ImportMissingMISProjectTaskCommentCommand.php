<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Task\Task;
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

#[AsCommand(name: 'legacy:import:missing_task_comment', description: 'Import missing MIS project tasks comments from legacy')]
class ImportMissingMISProjectTaskCommentCommand extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly SynchronizationVoter $syncVoter,
        private readonly ActivityLogVoter $activityLogVoter,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $taskCache = $this->cacheFactory->createEntityCache(Task::class, 'legacyId');

        $this->syncVoter->disable();
        $this->activityLogVoter->disable();

        // Import missing MIS Project Tasks Comments
        $sql = <<<'SQL'
                SELECT tasks_comments.id, tasks_comments.parent_id, tasks_comments.date, tasks_comments.poster, tasks_comments.comment
                FROM tasks_comments
                LEFT JOIN tasks ON tasks.id = tasks_comments.parent_id
                LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
                WHERE tasks.module = 'TTS'
                AND mis_tts.status <> 'QUEUE'
                AND tasks_comments.id NOT IN (SELECT legacy_id FROM tld_api.activity WHERE discr = 'comment' AND resource LIKE '%/tasks%')
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);

        $progressBar = new ProgressBar($output, $stmt->rowCount());
        $i = 0;
        foreach ($stmt->fetchAllAssociative() as $data) {
            $task = $taskCache->fetch((string) $data['parent_id']);
            if (null === $task) {
                $output->writeln(\sprintf('<error>Comment #%s not imported because Task linked not found</error>', $data['id']));
                continue;
            }

            $user = $peopleCache->fetch((string) $data['poster']);
            if (null === $user) {
                $output->writeln(\sprintf('<error>Comment #%s not imported because Poster not found</error>', $data['id']));
                continue;
            }

            $comment = new Comment();
            $comment
                ->setMessage(mb_trim($this->sanitationHelper->parse($data['comment'])))
                ->setCreatedAt(new \DateTime($data['date']))
                ->setUpdatedAt(new \DateTime($data['date']))
                ->setResource($this->iriConverter->getIriFromResource($task))
                ->setUser($peopleCache->fetch((string) $data['poster']))
                ->setLegacyId((int) $data['id'])
            ;

            ++$i;
            $progressBar->advance();
            $this->entityManager->persist($comment);
            $output->writeln(\sprintf('<error>Comment #%s imported</error>', $data['id']));
            if ($i > 10000) {
                $this->entityManager->flush();
                $i = 0;
            }
        }

        $progressBar->finish();
        $this->entityManager->flush();
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        $this->syncVoter->enable();
        $this->activityLogVoter->enable();

        return Command::SUCCESS;
    }
}
