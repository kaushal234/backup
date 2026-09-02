<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Task\Task;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:toc_task', description: 'Import Technician On Call tasks from legacy')]
class ImportTechnicianOnCallTaskCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

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
        $technicianOnCallCache = $this->cacheFactory->createEntityCache(TechnicianOnCall::class, 'legacyId');

        $sql = <<<SQL
            SELECT tasks.id, tasks.parent_id, tasks.assignor, tasks.status, tasks.date, tasks.task, tasks.assignee, tasks.due_date, tasks.ifactor, tasks.cat, tasks.reason
            FROM tasks
            WHERE tasks.module = 'TOC'
            AND parent_id in ($this->tocIdList)
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, Task::class, 'legacyId', 'id',
            function (Task $task, array $data) use ($moduleCacheByName, $peopleCache, $technicianOnCallCache) {
                if (null === ($technicianOnCall = $technicianOnCallCache->fetch((string) $data['parent_id']))) {
                    throw new \InvalidArgumentException(\sprintf('Task part of a Technician On Call #%d not imported', $data['parent_id']));
                }

                $task->referenceId = $technicianOnCall->getId();
                $task->module = $moduleCacheByName->fetch('TOC');
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
