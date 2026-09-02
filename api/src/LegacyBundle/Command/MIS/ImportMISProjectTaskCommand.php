<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\MIS\Project\Project;
use App\Entity\Module\Module;
use App\Entity\Task\Task;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:task', description: 'Import MIS project tasks from legacy')]
class ImportMISProjectTaskCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $moduleCacheByName = $this->cacheFactory->createEntityCache(Module::class, 'name');
        $misProjectCache = $this->cacheFactory->createEntityCache(Project::class, 'legacyId');

        $sql = <<<'SQL'
            SELECT tasks.id, tasks.parent_id, tasks.assignor, tasks.status, tasks.date, tasks.task, tasks.assignee, tasks.due_date, tasks.ifactor, tasks.cat, tasks.reason, mis_tts.category
            FROM tasks
            LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
            WHERE tasks.module = 'TTS' AND mis_tts.status <> 'QUEUE'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, Task::class, 'legacyId', 'id',
            function (Task $task, array $data) use ($moduleCacheByName, $peopleCache, $misProjectCache) {
                if (null === ($project = $misProjectCache->fetch((string) $data['parent_id']))) {
                    throw new \InvalidArgumentException('Task part of a project not imported');
                }

                $task->referenceId = $project->getId();
                $task->module = $moduleCacheByName->fetch('MIS');
                $task->legacyId = $data['id'];
                $task->setStatus('OPEN' === $data['status'] ? BaseTask::PENDING : $data['status']);
                $task->dueDate = new \DateTime($data['due_date']);
                $task->createdAt = new \DateTime($data['date']);
                $task->description = mb_trim($this->sanitationHelper->parse($data['task']));
                $task->shortDescription = mb_trim($this->sanitationHelper->parse(mb_substr($data['task'], 0, 100)));
                $task->createdBy = $peopleCache->fetch((string) $data['assignor']);
                $task->assignee = $peopleCache->fetch((string) $data['assignee']);
                $task->indiceFactor = null !== $data['ifactor'] ? \sprintf('IF %s', $data['ifactor']) : null;
            }
        );

        return Command::SUCCESS;
    }
}
