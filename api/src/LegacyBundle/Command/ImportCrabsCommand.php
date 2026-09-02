<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Parts\CrabPart;
use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Entity\Quality\CrabDepartment;
use App\Entity\Quality\NonConformity;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:crab')]
class ImportCrabsCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Imports Crab from legacy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $legacyPeopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $equipmentRecordCache = $this->cacheFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $nonConformityCache = $this->cacheFactory->createEntityCache(NonConformity::class, 'id');
        $crabCodeCache = $this->cacheFactory->createEntityCache(CrabCode::class, 'code');
        $crabDepartmentCache = $this->cacheFactory->createEntityCache(CrabDepartment::class, 'name');

        // Import Crab
        $sql = <<<'SQL'
            SELECT id, status, init_emno, fix_emno, insp_id, insp_dt, fix_dt, dt, erid, pn, opno, ncrid, code, dsca, act, dept,
            (SELECT mod_logs.comment FROM mod_logs WHERE mod_logs.module='CRAB'
            AND mod_logs.parent_id=crabs.id AND mod_logs.comment LIKE "Inspector's comments:%" LIMIT 1) AS inspectlog
            FROM crabs
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->setBatchSize(10_000);
        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, Crab::class, 'legacyId', 'id',
            function (Crab $crab, array $data) use ($legacyPeopleCache, $equipmentRecordCache, $nonConformityCache, $crabCodeCache, $crabDepartmentCache) {
                $metadata = $this->entityManager->getClassMetaData(Crab::class);
                $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                $metadata->setIdGenerator(new AssignedGenerator());

                $reflectionProperty = new \ReflectionProperty(Crab::class, 'id');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($crab, $data['id']);

                if (null !== $data['pn']) {
                    $crabPart = new CrabPart();
                    $crabPart->partNumber = $data['pn'];
                    $crabPart->description = '';
                    $crab->setPart($crabPart);
                }

                if (null !== $data['code']) {
                    $crab->code = $crabCodeCache->fetch((string) $data['code']);
                }

                if ('0000-00-00 00:00:00' !== $data['fix_dt']) {
                    $crab->fixedAt = (new \DateTime($data['fix_dt']));
                } else {
                    $crab->fixedAt = null;
                }

                if ('0000-00-00 00:00:00' !== $data['insp_dt']) {
                    $crab->inspectedAt = (new \DateTime($data['insp_dt']));
                } else {
                    $crab->fixedAt = null;
                }

                $crab->setLegacyId($data['id']);
                $crab->createdAt = new \DateTime($data['dt']);

                if ('CLOSED' === $data['status']) {
                    $crab->status = 'CLOSED';
                } elseif ('0000-00-00 00:00:00' !== $data['fix_dt']) {
                    $crab->status = 'TO-INSPECT';
                } else {
                    $crab->status = 'TO-FIX';
                }

                $crab->department = $crabDepartmentCache->fetch((string) $data['dept']) ?? $crabDepartmentCache->fetch('Unknown');

                $crab->description = mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['dsca'])));
                $crab->fixingComments = mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['act'])));
                $crab->createdBy = $legacyPeopleCache->fetch((string) $data['init_emno']);
                $crab->fixedBy = $legacyPeopleCache->fetch((string) $data['fix_emno']);
                $crab->inspectedBy = $legacyPeopleCache->fetch((string) $data['insp_id']);
                $crab->inspectingComments = null !== $data['inspectlog'] ? mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['inspectlog']))) : null;
                $crab->category = $data['opno'];
                $crab->equipmentRecord = $equipmentRecordCache->fetch((string) $data['erid']);
                $crab->nonConformity = $nonConformityCache->fetch((string) $data['ncrid']);
            },
        );

        return Command::SUCCESS;
    }
}
