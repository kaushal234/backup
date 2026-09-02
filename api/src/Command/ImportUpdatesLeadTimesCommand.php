<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Activity\Log;
use App\Entity\Directory\People;
use App\Entity\Manufacturing\LeadTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:update:lead_time', description: 'Import update data for lead times')]
class ImportUpdatesLeadTimesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $leadTimeRepository = $this->entityManager->getRepository(LeadTime::class);
        $peopleRepository = $this->entityManager->getRepository(People::class);
        foreach ($leadTimeRepository->findAll() as $leadTime) {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('l.resource')
                ->addSelect('l.updatedAt')
                ->addSelect('p.id as user')
                ->from(Log::class, 'l')
                ->leftJoin(People::class, 'p', Join::WITH, 'p = l.user')
                ->where($queryBuilder->expr()->eq('l.resource', ':iri'))
                ->orderBy('l.updatedAt', 'ASC')
                ->setParameter('iri', \sprintf('/lead_times/%s', $leadTime->getId()))
            ;

            foreach ($queryBuilder->getQuery()->getResult() as $log) {
                $leadTime->updatedAt = $log['updatedAt'];
                $leadTime->updatedBy = null !== $log['user'] ? $peopleRepository->find($log['user']) : null;
                $output->writeln(\sprintf('Lead time #%s updatedAt to %s and updatedBy to %s', $leadTime->getId(), $leadTime->updatedAt->format('Y-m-d'), $leadTime->updatedBy->getLastname()));
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
