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

#[AsCommand(name: 'legacy:import:technician_on_call:audit_log_status', description: 'Import status history')]
class ImportTechnicianOnCallAuditLogStatusCommand extends Command
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
        $startTime = microtime(true);
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $technicianOnCalls = $technicianOnCallRepository->createQueryBuilder('t')
            ->where('t.id in (:ids)')
            ->setParameter('ids', explode(',', $this->tocIdList))
            ->getQuery()
            ->getResult()
        ;

        $i = 0;
        /** @var TechnicianOnCall $technicianOnCall */
        foreach ($technicianOnCalls as $technicianOnCall) {
            $queryBuilder = $this->legacyConnection->createQueryBuilder();

            $queryBuilder
                ->select('ml.*')
                ->from('mod_logs', 'ml')
                ->where('ml.parent_id = :technicianOnCallLegacyId')
                ->andWhere('ml.log_num = :logNum')
                ->andWhere('ml.module = :module')
                ->setParameter('technicianOnCallLegacyId', $technicianOnCall->getLegacyId())
                ->setParameter('logNum', 10)
                ->setParameter('module', 'TOC')

            ;

            if ([] === $legacyLogs = $queryBuilder->fetchAllAssociative()) {
                $output->writeln(\sprintf('<comment>History for TOC id #%d not found</comment>', $technicianOnCall->getLegacyId()));
                continue;
            }

            usort($legacyLogs, static fn ($a, $b) => strtotime($a['date']) - strtotime($b['date']));

            $previous = $this->createLog(
                technicianOnCall: $technicianOnCall,
                property: 'status',
                value: TechnicianOnCall::PENDING,
                createdAt: $technicianOnCall->createdAt
            );
            $this->entityManager->persist($previous);

            foreach ($legacyLogs as $legacylog) {
                $auditLog = $this->createLog(
                    technicianOnCall: $technicianOnCall,
                    property: 'status',
                    value: $legacylog['comment'],
                    createdAt: new \DateTime($legacylog['date']),
                    previous: $previous
                );

                $this->entityManager->persist($auditLog);

                $previous = $auditLog;
            }

            $output->writeln(\sprintf('<info>History for TOC id #%d imported (%d log)</info>', $technicianOnCall->getLegacyId(), \count($legacyLogs)));

            ++$i;

            if ($i > 500) {
                $this->entityManager->flush();
                $output->writeln('<question>History flushed</question>');
                $i = 0;
            }
        }

        $this->entityManager->flush();

        $elapsed = microtime(true) - $startTime;
        $minutes = floor($elapsed / 60);
        $seconds = $elapsed % 60;

        $output->writeln(\sprintf('<info>Temps d\'exécution : %dm %ds</info>', $minutes, $seconds));

        return Command::SUCCESS;
    }

    private function createLog(
        TechnicianOnCall $technicianOnCall,
        string $property,
        string $value,
        \DateTime $createdAt,
        ?AuditLog $previous = null,
    ) {
        $value = match ($value) {
            'OPEN' => TechnicianOnCall::PENDING,
            'IN_PROGRESS',
            'IN PROGRESS' => TechnicianOnCall::IN_PROGRESS,
            default => $value,
        };

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
