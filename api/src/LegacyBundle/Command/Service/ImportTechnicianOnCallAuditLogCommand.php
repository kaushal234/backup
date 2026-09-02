<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\AuditLog;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:technician_on_call:audit_log', description: 'Import change date for closed status and factory flag')]
class ImportTechnicianOnCallAuditLogCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<SQL
                SELECT id, dt, dt_closed, factory_support_required_at, factory_support_given_at, status
                FROM toc
                WHERE (factory_support_required_at is not null and factory_support_required_at != '')
                AND id in ({$this->tocIdList})
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $i = 0;
        while ($data = $stmt->fetchAssociative()) {
            $technicianOnCall = $technicianOnCallRepository->findOneBy(['legacyId' => $data['id']]);

            if (!$technicianOnCall) {
                continue;
            }

            $output->writeln(\sprintf('Audit for TOC id #%d', $technicianOnCall->getId()));

            if (!empty($data['factory_support_required_at'])) {
                $requestFactoryFlagLog = $this->createLog($technicianOnCall, 'factoryFlag', (string) true, new \DateTime($data['factory_support_required_at']));
                $this->entityManager->persist($requestFactoryFlagLog);

                if (!empty($data['factory_support_given_at']) || \in_array($data['status'], TechnicianOnCall::CLOSED_STATUSES, true)) {
                    $responseFactoryFlagLog = $this->createLog(
                        $technicianOnCall,
                        'factoryFlag',
                        (string) false,
                        $data['factory_support_required_at'] > $data['factory_support_given_at'] ? new \DateTime($data['factory_support_required_at']) : new \DateTime($data['factory_support_given_at']),
                        $requestFactoryFlagLog
                    );
                    $this->entityManager->persist($responseFactoryFlagLog);
                }
            }

            ++$i;

            if ($i > 500) {
                $this->entityManager->flush();
                $i = 0;
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function createLog(
        TechnicianOnCall $technicianOnCall,
        string $property,
        string $value,
        \DateTime $createdAt,
        ?AuditLog $previous = null,
    ) {
        $auditLog = new AuditLog();
        $auditLog->auditType = 'technician_on_call';
        $auditLog->createdAt = $createdAt;
        $auditLog->property = $property;
        $auditLog->referenceId = $technicianOnCall->getId();
        $auditLog->value = $value;
        $auditLog->createdBy = null;

        if ($previous) {
            $auditLog->setPrevious($previous);
        }

        return $auditLog;
    }
}
