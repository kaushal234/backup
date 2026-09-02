<?php

declare(strict_types=1);

namespace App\Command\Quality\NonConformity;

use App\Entity\Activity\Log;
use App\Entity\Quality\NonConformity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:rejected:ncr', description: 'Import closed at date for rejected NCR')]
class ImportClosedAtForRejectedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nonConformityRepository = $this->entityManager->getRepository(NonConformity::class);
        foreach ($nonConformityRepository->findBy(['status' => NonConformity::REJECTED]) as $nonConformity) {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('l.resource')
                ->addSelect('l.changeSet')
                ->addSelect('l.createdAt')
                ->from(Log::class, 'l')
                ->where($queryBuilder->expr()->eq('l.resource', ':iri'))
                ->orderBy('l.createdAt', 'ASC')
                ->setParameter('iri', \sprintf('/quality/non_conformities/%s', $nonConformity->getId()))
            ;

            foreach ($queryBuilder->getQuery()->getResult() as $log) {
                if (!isset($log['changeSet']['status'])) {
                    continue;
                }

                if (NonConformity::REJECTED !== $log['changeSet']['status'][1]) {
                    continue;
                }

                $nonConformity->closedAt = $log['createdAt'];
                $output->writeln(\sprintf('NCR #%s closed at updated to %s', $nonConformity->getId(), $nonConformity->closedAt->format('Y-m-d')));
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
