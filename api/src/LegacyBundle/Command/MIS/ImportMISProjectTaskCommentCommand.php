<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Task\Task;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:task_comment', description: 'Import MIS project tasks comments from legacy')]
class ImportMISProjectTaskCommentCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
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

        // Import MIS Project Tasks Comments
        $sql = <<<'SQL'
                SELECT tasks_comments.id, tasks_comments.parent_id, tasks_comments.date, tasks_comments.poster, tasks_comments.comment
                FROM tasks_comments
                LEFT JOIN tasks ON tasks.id = tasks_comments.parent_id
                LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
                WHERE tasks.module = 'TTS' AND mis_tts.status <> 'QUEUE'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);

        $this->helper->disableValidation();
        $this->helper->setBatchSize(10_000);

        $this->helper->progressiveImport(
            $output, $stmt, Comment::class, 'legacyId', 'id',
            function (Comment $comment, array $data) use ($peopleCache, $taskCache, $output) {
                $task = $taskCache->fetch((string) $data['parent_id']);

                if (null === $task) {
                    $output->writeln(\sprintf('<error>Comment #%s not imported because Task linked not found</error>', $data['id']));
                    throw new \InvalidArgumentException('TTS not found');
                }

                $user = $peopleCache->fetch((string) $data['poster']);
                if (null === $user) {
                    $output->writeln(\sprintf('<error>Comment #%s not imported because Poster not found</error>', $data['id']));
                    throw new \InvalidArgumentException('Poster not found');
                }

                $comment
                    ->setMessage(mb_trim($this->sanitationHelper->parse($data['comment'])))
                    ->setCreatedAt(new \DateTime($data['date']))
                    ->setUpdatedAt(new \DateTime($data['date']))
                    ->setResource($this->iriConverter->getIriFromResource($task))
                    ->setUser($peopleCache->fetch((string) $data['poster']))
                ;
            }, false, null, true
        );

        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        return Command::SUCCESS;
    }
}
