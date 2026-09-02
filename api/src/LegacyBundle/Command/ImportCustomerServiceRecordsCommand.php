<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\EquipmentRecord;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:service:csr')]
class ImportCustomerServiceRecordsCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Imports Customer Service Records from legacy');
    }

    public function getFirstValidDate(array $dates): ?\DateTime
    {
        foreach ($dates as $date) {
            if ('0000-00-00 00:00:00' !== $date) {
                return new \DateTime($date);
            }
        }

        return null;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arrayResult = [
            'CsrMissingDate' => [],
            'CsrPendingMissingTech' => [],
            'CsrProgressMissingTech' => [],
            'CsrCompletedMissingTech' => [],
            'CsrClosedMissingTech' => [],
            'badErArray' => [],
            'badSbArray' => [],
            'badTocArray' => [],
            'notImported' => [],
        ];

        $countByType = [
            'CustomerServiceRecord' => 0,
            'ServiceBulletinCustomerServiceRecord' => 0,
            'TechnicianOnCallCustomerServiceRecord' => 0,
            'CommissioningCustomerServiceRecord' => 0,
        ];

        $legacyPeopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $equipmentRecordCache = $this->cacheFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $airportCache = $this->cacheFactory->createEntityCache(Airport::class, 'code');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');
        $logger = new ConsoleLogger($output);
        $this->helper->setBatchSize(10000);

        // 408 CSR SB (tous closed) ? quelles règles d'import ? => ne pas importer
        foreach (['TOC' => TechnicianOnCallCustomerServiceRecord::class, 'SB3' => ServiceBulletinCustomerServiceRecord::class, '' => CustomerServiceRecord::class, 'Commissioning' => CommissioningCustomerServiceRecord::class] as $module => $class) {
            $andWhere = \sprintf("module = '%s'", $module);
            if ('Commissioning' === $module) {
                $andWhere = "work_type = 'Commissioning' AND module = ''";
            }
            if ('' === $module) {
                $andWhere = "work_type != 'Commissioning' AND module = ''";
            }
            // Import CSR
            $sql = <<<SQL
                SELECT id, entered_by, dt, status, work_type, tech_id, dt_work, dt_sche, dt_completed, dt_closed, apc, short_desc, int_desc, module_id, parent_id, module, send_tech, sso_id
                FROM csr
                WHERE module not in ('SRVO', 'SB')
                  AND ($andWhere)
                SQL;

            $stmt = $this->legacyConnection->executeQuery($sql, ['module' => $module]);

            $this->helper->disableValidation();
            $this->helper->progressiveImport(
                $output, $stmt, $class, 'legacyId', 'id',
                function (AbstractCustomerServiceRecord $customerServiceRecord, array $data) use (&$countByType, &$arrayResult, $class, $locationCache, $legacyPeopleCache, $equipmentRecordCache, $airportCache, $logger) {
                    // do not import CSRs with an invalid ER
                    if (null === ($equipmentRecord = $equipmentRecordCache->fetch((string) $data['parent_id']))) {
                        $arrayResult['badErArray'][] = $customerServiceRecord->getLegacyId();
                        throw new \InvalidArgumentException('CSR not imported cause of bad ER link'.$data['id']);
                    }
                    $metadata = $this->entityManager->getClassMetaData($class);
                    $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                    $metadata->setIdGenerator(new AssignedGenerator());

                    $metadata = $this->entityManager->getClassMetaData(AbstractCustomerServiceRecord::class);
                    $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                    $metadata->setIdGenerator(new AssignedGenerator());

                    $reflectionProperty = new \ReflectionProperty(AbstractCustomerServiceRecord::class, 'id');
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($customerServiceRecord, $data['id']);

                    // CSR Type
                    // SB
                    if ($customerServiceRecord instanceof ServiceBulletinCustomerServiceRecord) {
                        $queryBuilder = $this->legacyConnection->createQueryBuilder();

                        $queryBuilder
                            ->select('id, parent_id, status')
                            ->from('sb_lines', 'sbl')
                            ->where('csr_id = :csrId')
                            ->setParameter('csrId', $customerServiceRecord->getLegacyId())
                        ;

                        $sbLine = $queryBuilder->executeQuery()->fetchAssociative();

                        if (!$sbLine) {
                            $arrayResult['badSBArray'][] = $customerServiceRecord->getLegacyId();
                            throw new \InvalidArgumentException('CSR not imported cause of SB Line not found'.$data['id']);
                        }

                        if (!\array_key_exists('parent_id', $sbLine)) {
                            $arrayResult['badSBArray'][] = $customerServiceRecord->getLegacyId();
                            throw new \InvalidArgumentException('CSR not imported cause of SB parent of SB Line not found'.$data['id']);
                        }

                        if ('CLOSED' === $sbLine['status'] && 'CLOSED' !== $data['status']) {
                            $data['status'] = 'CLOSED';
                            $logger->info('CSR not closed, but with parent SB closed. considered as status closed '.$data['id']);
                        }

                        $customerServiceRecord->serviceBulletinLinesLegacyId = $sbLine['id'];
                        $customerServiceRecord->serviceBulletinLegacyId = $sbLine['parent_id'];
                        ++$countByType['ServiceBulletinCustomerServiceRecord'];
                    }

                    // TOC
                    if ($customerServiceRecord instanceof TechnicianOnCallCustomerServiceRecord) {
                        // When TOC link is invalid, create default csr
                        $customerServiceRecord->tocLegacyId = $data['module_id'];
                        ++$countByType['TechnicianOnCallCustomerServiceRecord'];

                        $queryBuilder = $this->legacyConnection->createQueryBuilder();

                        $queryBuilder
                            ->select('toc.status')
                            ->from('toc', 'toc')
                            ->where('id = :tocId')
                            ->setParameter('tocId', $customerServiceRecord->tocLegacyId)
                        ;

                        $tocLegacyStatus = $queryBuilder->executeQuery()->fetchOne();
                        if (\in_array($tocLegacyStatus, ['CLOSED', 'SOLVED'], true) && !\in_array(
                            $data['status'],
                            AbstractCustomerServiceRecord::CLOSED_STATUSES,
                            true
                        )) {
                            $data['status'] = AbstractCustomerServiceRecord::COMPLETED;
                            $logger->info('CSR not closed, but with parent TOC closed. considered as status closed '.$data['id']);
                        }
                    }

                    // Commissioning
                    if ($customerServiceRecord instanceof CommissioningCustomerServiceRecord) {
                        ++$countByType['CommissioningCustomerServiceRecord'];
                    }

                    // Vanilla
                    if ($customerServiceRecord instanceof CustomerServiceRecord) {
                        ++$countByType['CustomerServiceRecord'];
                    }

                    // plannedAt
                    if ('0000-00-00 00:00:00' !== $data['dt_sche']) {
                        $customerServiceRecord->plannedAt = (new \DateTime($data['dt_sche']));
                    }

                    $customerServiceRecord->equipmentRecord = $equipmentRecord;
                    $customerServiceRecord->setLegacyId($data['id']);
                    $customerServiceRecord->title = mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['short_desc'])));
                    $customerServiceRecord->description = empty($data['int_desc']) ? null : mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['int_desc'])));

                    if ($createdBy = $legacyPeopleCache->fetch((string) $data['entered_by'])) {
                        $customerServiceRecord->createdBy = $createdBy;
                    }
                    $customerServiceRecord->equipmentRecord = $equipmentRecordCache->fetch((string) $data['parent_id']);
                    if ($airport = $airportCache->fetch($data['apc']) ?? $customerServiceRecord->equipmentRecord->getAirport()) {
                        $customerServiceRecord->setAirport($airport);
                    }

                    if (null === $this->getFirstValidDate([$data['dt_sche'], $data['dt_work'], $data['dt_completed'], $data['dt_closed'], $data['dt']])) {
                        $arrayResult['CsrMissingDate'][] = $customerServiceRecord->getLegacyId();
                        throw new \InvalidArgumentException('CSR not imported cause of no date '.$data['id']);
                    }

                    $customerServiceRecord->createdAt = $this->getFirstValidDate([$data['dt'], $data['dt_sche'], $data['dt_work'], $data['dt_completed'], $data['dt_closed']]);

                    // Status du CSR + intervention + plannedAt

                    // CLOSED
                    // 174 277 CSR ont le status closed, 137 130 sans SRVO, 118468 avec un tech_id, 73590 ont toutes les dates
                    if ('CLOSED' === $data['status']) {
                        $customerServiceRecord->setStatus('CLOSED');
                        $customerServiceRecord->closedAt = $this->getFirstValidDate([$data['dt_closed'], $data['dt_completed'], $data['dt_work'], $data['dt_sche'], $data['dt']]) ?? new \DateTime('2013-10-18 00:00:00');
                        $customerServiceRecord->completedAt = $this->getFirstValidDate([$data['dt_completed'], $data['dt_work'], $data['dt_sche'], $data['dt_closed'], $data['dt']]) ?? new \DateTime('2013-10-18 00:00:00');

                        if (0 === $data['tech_id']) {
                            $logger->info('CSR closed imported without intervention  '.$data['id']);

                            return;
                        }

                        if (null === ($leader = $legacyPeopleCache->fetch((string) $data['tech_id']))) {
                            if (null === $data['sso_id']) {
                                $arrayResult['CsrClosedMissingTech'][] = $customerServiceRecord->getLegacyId();
                                throw new \InvalidArgumentException('Close CSR not imported cause of bad tech '.$data['id']);
                            }
                            $sso = $locationCache->fetch((string) $data['sso_id']);
                            $position = $this->entityManager->getRepository(Position::class)->findOneBy(['code' => 'CSM']);
                            $leader = $this->entityManager->getRepository(People::class)->findOneByPositionByLocation($position, $sso);
                        }

                        $intervention = new Intervention();
                        $intervention->leader = $leader;
                        $intervention->setStatus(Intervention::SOLVED);
                        $customerServiceRecord->addIntervention($intervention);
                        $intervention->customerServiceRecord = $customerServiceRecord;
                        $intervention->plannedBy = $customerServiceRecord->createdBy;
                        $intervention->plannedAt = $this->getFirstValidDate([$data['dt_sche'], $data['dt_work'], $data['dt_completed'], $data['dt_closed'], $data['dt']]);
                        $intervention->startedAt = $this->getFirstValidDate([$data['dt_work'], $data['dt_sche'], $data['dt_completed'], $data['dt_closed'], $data['dt']]);
                        $intervention->endedAt = $this->getFirstValidDate([$data['dt_completed'], $data['dt_work'], $data['dt_sche'], $data['dt_closed'], $data['dt']]);

                        $logger->info('CSR closed imported with intervention '.$data['id']);

                        return;
                    }

                    // COMPLETED
                    // 19 105 ont le status completed, dont 196 sans dt completed et le plus récent date de 2020 (id 155548)
                    if ('COMPLETED' === $data['status']) {
                        $customerServiceRecord->setStatus('COMPLETED');
                        $customerServiceRecord->completedAt = $this->getFirstValidDate([$data['dt_completed'], $data['dt_closed'], $data['dt_work'], $data['dt_sche'], $data['dt']]) ?? new \DateTime('2013-10-18 00:00:00');

                        if (0 === $data['tech_id']) {
                            $logger->info('CSR completed imported without intervention  '.$data['id']);

                            return;
                        }

                        if (null === ($leader = $legacyPeopleCache->fetch((string) $data['tech_id']))) {
                            if (null === $data['sso_id']) {
                                $arrayResult['CsrCompletedMissingTech'][] = $customerServiceRecord->getLegacyId();
                                throw new \InvalidArgumentException('Completed CSR not imported cause of bad tech '.$data['id']);
                            }
                            $sso = $locationCache->fetch((string) $data['sso_id']);
                            $position = $this->entityManager->getRepository(Position::class)->findOneBy(['code' => 'CSM']);
                            $leader = $this->entityManager->getRepository(People::class)->findOneByPositionByLocation($position, $sso);
                        }

                        $intervention = new Intervention();
                        $intervention->leader = $leader;
                        $intervention->setStatus(Intervention::SOLVED);
                        $customerServiceRecord->addIntervention($intervention);
                        $intervention->customerServiceRecord = $customerServiceRecord;
                        $intervention->plannedBy = $customerServiceRecord->createdBy;
                        $intervention->plannedAt = $this->getFirstValidDate([$data['dt_sche'], $data['dt_work'], $data['dt_completed'], $data['dt_closed'], $data['dt']]);
                        $intervention->startedAt = $this->getFirstValidDate([$data['dt_work'], $data['dt_sche'], $data['dt_completed'], $data['dt_closed'], $data['dt']]);
                        $intervention->endedAt = $this->getFirstValidDate([$data['dt_completed'], $data['dt_work'], $data['dt_sche'], $data['dt_closed'], $data['dt']]);

                        $logger->info('CSR completed imported with intervention '.$data['id']);

                        return;
                    }

                    // IN PROGRESS
                    // 7869 CSR ont le status in progress 595 n'ont pas de dt_work et 387 CSR n'ont ni dt_work ni dt_sche. Parmi ces 595 csr, 2 n'ont pas de tech_id. Sinon, 2020 CSR n'ont pas de tech_id
                    if ('IN PROGRESS' === $data['status']) {
                        if (0 === $data['tech_id'] || null === $data['tech_id']) {
                            $customerServiceRecord->setStatus('PLANNED');

                            if (null === $customerServiceRecord->plannedAt) {
                                $customerServiceRecord->setStatus('PENDING');
                                $logger->info('CSR in progress imported in status pending  '.$data['id']);

                                return;
                            }
                            $logger->info('CSR in progress imported in status planned  '.$data['id']);

                            return;
                        }

                        if (null === ($leader = $legacyPeopleCache->fetch((string) $data['tech_id']))) {
                            $arrayResult['CsrProgressMissingTech'][] = $customerServiceRecord->getLegacyId();
                            throw new \InvalidArgumentException('in progress CSR not imported cause of bad tech '.$data['id']);
                        }

                        // When tech but no date , import CSR in pool
                        if ('0000-00-00 00:00:00' === $data['dt_sche'] && '0000-00-00 00:00:00' === $data['dt_work']) {
                            $customerServiceRecord->setStatus('PENDING');
                            $logger->info('CSR in progress imported in status pending without knowing tech  '.$data['id']);

                            return;
                        }

                        $intervention = new Intervention();
                        $intervention->setStatus('PENDING');
                        $customerServiceRecord->setStatus('ASSIGNED');
                        $intervention->leader = $leader;
                        $intervention->plannedBy = $customerServiceRecord->createdBy;
                        $intervention->customerServiceRecord = $customerServiceRecord;
                        $customerServiceRecord->addIntervention($intervention);

                        // 593 cas
                        if ('0000-00-00 00:00:00' === $data['dt_sche'] && '0000-00-00 00:00:00' !== $data['dt_work']) {
                            $customerServiceRecord->plannedAt = (new \DateTime($data['dt_work']));
                            $intervention->startedAt = (new \DateTime($data['dt_work']));
                            $intervention->setStatus('STARTED');
                            $customerServiceRecord->setStatus('IN-PROGRESS');
                            $logger->info('CSR in progress imported with plannedAt = dt_work with intervention started '.$data['id']);
                        }

                        $intervention->plannedAt = $customerServiceRecord->plannedAt;
                        $logger->info('CSR in progress imported with intervention pending'.$data['id']);

                        return;
                    }

                    // PENDING
                    // 4585 CSR avec status pending, 0 avec dt_closed, 0 avec dt_completed, 952 avec dt_work, 2072 avec dt_sche, 945 avec les deux, avec aucune des deux
                    // 2297 CSR pending avec tech_id dont 1340 avec dt_sche et 529 avec une dt_work
                    if ('PENDING' === $data['status']) {
                        if (0 === $data['tech_id'] || null === $data['tech_id']) {
                            $customerServiceRecord->setStatus('PLANNED');
                            if (null !== $customerServiceRecord->plannedAt) {
                                $logger->info('CSR pending imported in status planned  '.$data['id']);

                                return;
                            }
                            if ('0000-00-00 00:00:00' !== $data['dt_work']) {
                                $customerServiceRecord->plannedAt = (new \DateTime($data['dt_work']));
                                $logger->info('CSR pending imported with plannedAt = dt_work  '.$data['id']);

                                return;
                            }

                            $customerServiceRecord->setStatus('PENDING');
                            $logger->info('CSR pending imported in status pending  '.$data['id']);

                            return;
                        }

                        // 951 cas
                        if ('0000-00-00 00:00:00' === $data['dt_work'] && '0000-00-00 00:00:00' === $data['dt_sche']) {
                            $customerServiceRecord->setStatus('PENDING');
                            $logger->info('CSR pending imported in status pending without knowing tech  '.$data['id']);

                            return;
                        }

                        if (null === ($leader = $legacyPeopleCache->fetch((string) $data['tech_id']))) {
                            $arrayResult['CsrPendingMissingTech'][] = $customerServiceRecord->getLegacyId();
                            throw new \InvalidArgumentException('pending CSR not imported cause of bad tech '.$data['id']);
                        }

                        $intervention = new Intervention();
                        $intervention->leader = $leader;
                        $intervention->customerServiceRecord = $customerServiceRecord;
                        $customerServiceRecord->addIntervention($intervention);
                        $intervention->plannedBy = $customerServiceRecord->createdBy;
                        $logger->info('CSR pending imported with intervention '.$data['id']);

                        if ('0000-00-00 00:00:00' !== $data['dt_work']) {
                            $customerServiceRecord->setStatus('IN-PROGRESS');
                            $intervention->setStatus('STARTED');
                            $intervention->startedAt = (new \DateTime($data['dt_work']));
                            if (null === $customerServiceRecord->plannedAt) {
                                $customerServiceRecord->plannedAt = (new \DateTime($data['dt_work']));
                            }
                            $intervention->plannedAt = $customerServiceRecord->plannedAt;

                            return;
                        }

                        if (null !== $customerServiceRecord->plannedAt) {
                            $customerServiceRecord->setStatus('ASSIGNED');
                            $intervention->setStatus('PENDING');
                            $intervention->plannedAt = $customerServiceRecord->plannedAt;

                            return;
                        }

                        return;
                    }
                    $arrayResult['notImported'][] = $customerServiceRecord->getLegacyId();
                    throw new \InvalidArgumentException('This CSR has not been imported '.(string) $customerServiceRecord->getLegacyId());
                },
            );
        }
        $this->entityManager->flush();

        $maxLength = max(array_map('count', $arrayResult));

        foreach ($arrayResult as $key => $value) {
            if (\count($value) < $maxLength) {
                $arrayResult[$key] = array_pad($value, $maxLength, null);
            }
        }

        $headers = array_keys($arrayResult);
        $rows = [];

        for ($i = 0; $i < $maxLength; ++$i) {
            $row = [];
            foreach ($arrayResult as $key => $values) {
                $row[] = $values[$i];
            }
            $rows[] = $row;
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();

        $output->writeln("\nTotals CSR not imported:");
        foreach ($arrayResult as $key => $values) {
            $total = \count(array_filter($values, static function ($value) {
                return null !== $value;
            }));
            $output->writeln(\sprintf('%s: %d', $key, $total));
        }

        $output->writeln("\nTotals CSR imported by type:");
        foreach ($countByType as $type => $count) {
            $output->writeln(\sprintf('%s: %d', $type, $count));
        }

        return Command::SUCCESS;
    }
}
