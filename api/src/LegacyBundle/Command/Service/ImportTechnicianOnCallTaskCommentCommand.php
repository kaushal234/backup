<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\EventListener\EntityChangeListener;
use App\Doctrine\Utils\ListenerManager;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Task\Task;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\SanitationHelper;
use LegacyBundle\Doctrine\EventListener\PersistenceSubscriber;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:toc_task_comment', description: 'Import Technician On Call tasks comments from legacy')]
class ImportTechnicianOnCallTaskCommentCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly ListenerManager $listenerManager,
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

        // Import Technician On Call Tasks Comments
        $sql = <<<SQL
                SELECT tasks_comments.id, tasks_comments.parent_id, tasks_comments.date, tasks_comments.poster, tasks_comments.comment
                FROM tasks_comments
                LEFT JOIN tasks ON tasks.id = tasks_comments.parent_id
                WHERE tasks.module = 'TOC'
                AND tasks.parent_id in ($this->tocIdList)
            SQL;

        $stmt = $this->legacyConnection->prepare($sql);
        $result = $stmt->executeQuery();

        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);

        $this->entityManager->getEventManager()->removeEventListener('prePersist', $this->entityManager->getUnitOfWork());

        $current = 0;

        while ($data = $result->fetchAssociative()) {
            $task = $taskCache->fetch((string) $data['parent_id']);

            if (null === $task) {
                $output->writeln(\sprintf('<error>Comment #%s not imported because Task linked not found</error>', $data['id']));
                continue;
            }

            if ($task->getId() !== $current) {
                $current = $task->getId();
                // Flush all comments of the same task together
                $this->flush();
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
                ->setLegacyId($data['id'])
            ;

            $this->persist($comment);
            $output->writeln(\sprintf('<info>Comment #%d imported for task #%d</info>', $data['id'], $data['parent_id']));
        }
        $this->flush();

        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        return Command::SUCCESS;
    }

    private function persist(object $entity)
    {
        $eventManager = $this->entityManager->getEventManager();

        $listenerToRemove = null;
        $listeners = $eventManager->getListeners(Events::prePersist);

        foreach ($listeners as $listener) {
            if ($listener instanceof PersistenceSubscriber) {
                $listenerToRemove = $listener;
                break;
            }
        }

        if ($listenerToRemove) {
            $eventManager->removeEventListener([
                Events::prePersist,
                Events::preUpdate,
                Events::preRemove,
                Events::onFlush,
                Events::postFlush,
            ], $listenerToRemove);
        }

        try {
            $this->entityManager->persist($entity);
        } finally {
            if ($listenerToRemove) {
                $eventManager->addEventListener([
                    Events::prePersist,
                    Events::preUpdate,
                    Events::preRemove,
                    Events::onFlush,
                    Events::postFlush,
                ], $listenerToRemove);
            }
        }
    }

    private function flush()
    {
        $eventManager = $this->entityManager->getEventManager();

        $listenerToRemove = null;
        $listeners = $eventManager->getListeners(Events::prePersist);

        foreach ($listeners as $listener) {
            if ($listener instanceof PersistenceSubscriber) {
                $listenerToRemove = $listener;
                break;
            }
        }

        if ($listenerToRemove) {
            $eventManager->removeEventListener([
                Events::prePersist,
                Events::preUpdate,
                Events::preRemove,
                Events::onFlush,
                Events::postFlush,
            ], $listenerToRemove);
        }

        $this->listenerManager->removeListener($this->entityManager, [EntityChangeListener::class]);

        try {
            $this->entityManager->flush();
        } finally {
            if ($listenerToRemove) {
                $eventManager->addEventListener([
                    Events::prePersist,
                    Events::preUpdate,
                    Events::preRemove,
                    Events::onFlush,
                    Events::postFlush,
                ], $listenerToRemove);
            }
        }
    }
}
