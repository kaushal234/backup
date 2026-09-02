<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\MIS\Project\Phase;
use App\Entity\MIS\Project\Project;
use App\Entity\MIS\Project\ProjectTag;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:projects', description: 'Import Projects from legacy')]
class ImportProjectCommand extends Command
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
        $businessUnitCache = $this->cacheFactory->createEntityCache(BusinessUnit::class, 'legacyId');
        $tagCache = $this->cacheFactory->createEntityCache(ProjectTag::class, 'name');

        $sql = <<<'SQL'
            SELECT id, category, location, owner, assignee, ifactor, budget_hours, dt_opened, IF(eta = '0000-00-00 00:00:00', NULL, eta) as estimated_closure, IF(dt_closed = '0000-00-00 00:00:00', NULL, dt_closed) as close_date, problem, status, solution
            FROM mis_tts
            WHERE status <> 'QUEUE'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, Project::class, 'legacyId', 'id',
            function (Project $project, array $data) use ($businessUnitCache, $peopleCache, $tagCache) {
                if ('BAAN' === $data['category']) {
                    throw new \InvalidArgumentException('Project not imported');
                }
                $project->indicesFactor = null !== $data['ifactor'] ? \sprintf('IF %s', $data['ifactor']) : null;
                $project->name = mb_trim($this->sanitationHelper->parse(mb_substr($data['problem'], 0, 100)));
                $project->description = mb_trim($this->sanitationHelper->parse($data['problem']));
                $project->createdAt = new \DateTime($data['dt_opened']);
                $project->startedAt = new \DateTime($data['dt_opened']);

                $project->projectManager = $peopleCache->fetch((string) $data['owner']);
                $project->misOwner = $peopleCache->fetch((string) $data['assignee']);
                $businessUnit = $businessUnitCache->fetch((string) $data['location']);
                $project->region = $businessUnit->getRegion();

                if (1 === (int) $data['ifactor'] && Project::PHASE_0 === $data['status']) {
                    $project->setStatus(Project::PENDING);
                } else {
                    $project->setStatus($data['status']);
                }

                for ($i = 0; $i < 5; ++$i) {
                    $phaseToCreate = new Phase();
                    $phaseToCreate->number = $i;
                    $phaseToCreate->estimatedHours = (int) ($data['budget_hours'] / 5);

                    if (4 === $i) {
                        if (null !== $data['estimated_closure']) {
                            $phaseToCreate->estimatedClosureAt = new \DateTime($data['estimated_closure']);
                        }

                        if (null !== $data['close_date']) {
                            $phaseToCreate->revisedClosureAt = new \DateTime($data['close_date']);
                        }
                    } else {
                        if (null !== $data['estimated_closure']) {
                            switch ($i) {
                                case 0:
                                    $months = 8;
                                    break;
                                case 1:
                                    $months = 6;
                                    break;
                                case 2:
                                    $months = 4;
                                    break;
                                default:
                                    $months = 2;
                            }

                            $phaseToCreate->estimatedClosureAt = (new \DateTime($data['estimated_closure']))->modify(\sprintf('- %d months', $months));
                        }
                    }
                    $project->addPhase($phaseToCreate);
                }

                if (Project::CLOSED === $project->getStatus()) {
                    $project->conclusion = \sprintf('Problem: %s \r Solution: %s', mb_trim($this->sanitationHelper->parse($data['problem'])), mb_trim($this->sanitationHelper->parse($data['solution'])));
                }
                if (null !== $data['category'] && '' !== mb_trim($data['category']) && '0' !== $data['category']) {
                    $project->addTag($tagCache->fetch($data['category']));
                }
            }
        );

        return Command::SUCCESS;
    }
}
