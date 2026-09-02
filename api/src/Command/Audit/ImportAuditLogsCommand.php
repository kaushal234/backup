<?php

declare(strict_types=1);

namespace App\Command\Audit;

use App\Entity\Activity\Log;
use App\Entity\AuditLog;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Util\Iri;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:import:audit_logs', description: 'Import existing logs into Audit Log table')]
class ImportAuditLogsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $troubleTicketRepository = $this->entityManager->getRepository(TroubleTicket::class);
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $i = 0;
        foreach ($troubleTicketRepository->findAll() as $troubleTicket) {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('l.resource')
                ->addSelect('l.changeSet')
                ->addSelect('l.createdAt')
                ->addSelect('p.id as createdBy')
                ->from(Log::class, 'l')
                ->leftJoin(People::class, 'p', Join::WITH, 'p = l.user')
                ->where($queryBuilder->expr()->eq('l.resource', ':iri'))
                ->orderBy('l.createdAt', 'ASC')
                ->setParameter('iri', \sprintf('/mis/trouble_tickets/%s', $troubleTicket->getId()))
            ;

            $previouslyCreated = null;
            foreach ($queryBuilder->getQuery()->getResult() as $log) {
                if (!isset($log['changeSet']['status'])) {
                    continue;
                }

                $auditLog = new AuditLog();
                $auditLog->auditType = 'trouble_ticket';
                $auditLog->createdAt = $log['createdAt'];
                $auditLog->property = 'status';
                $auditLog->referenceId = (int) Iri::id($log['resource']);
                $auditLog->value = 'RESOLVED' === $log['changeSet']['status'][1] ? 'SOLVED' : $log['changeSet']['status'][1];
                $auditLog->createdBy = null !== $log['createdBy'] ? $peopleRepository->find($log['createdBy']) : null;

                $this->entityManager->persist($auditLog);

                if (null !== $previouslyCreated) {
                    $auditLog->setPrevious($previouslyCreated);
                }

                $previouslyCreated = $auditLog;
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
}
