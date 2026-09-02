<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\HumanResources\Job;
use App\Entity\Module\Module;
use App\Repository\Module\ModuleRepository;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:jobs')]
class ImportJobsCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly ModuleRepository $moduleRepository;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper, ModuleRepository $moduleRepository)
    {
        parent::__construct();
        $this->setDescription('Import jobs from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $businessUnitCache = $this->cacheFactory->createEntityCache(BusinessUnit::class, 'legacyId');

        /** @var Module $fcrModule */
        $fcrModule = $this->moduleRepository->findByName('JOB');
        $moo = $fcrModule->getOperationalOwner();

        // Import countries
        $sql = <<<'SQL'
            SELECT *
            FROM hr_jobs;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Job::class, 'legacyId', 'id',
            function (Job $job, array $data) use ($peopleCache, $businessUnitCache, $moo) {
                if (null === ($assignor = $peopleCache->fetch($data['assignor']))) {
                    $assignor = $moo;
                }
                $job
                    ->setLegacyId((int) $data['id'])
                    ->setBusinessUnit($businessUnitCache->fetch($data['buid']))
                    ->setCreatedAt(new \DateTime($data['date']))
                    ->setCreatedBy($assignor)
                    ->setDescription($this->sanitationHelper->parse($data['description']))
                    ->setDiploma($this->sanitationHelper->parse($data['diploma']))
                    ->setExperience($this->sanitationHelper->parse($data['experience']))
                    ->setTitle($this->sanitationHelper->parse($data['title']))
                    ->setEnabled('OPEN' === $data['status'])
                ;
            }, true
        );

        return 0;
    }
}
