<?php

declare(strict_types=1);

namespace App\Command\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Manager\EntityPropertiesTranslationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'api:technician_on_call:translate_closing_fields_when_null',
    description: 'When a technician on call is closed, translated fields are empty and original closing fields are not empty, translate the closing fields to English language'
)]
class TechnicianOnCallTranslateClosingFieldsWhenNullCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityPropertiesTranslationManager $translationManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $queryBuilder = $repository->createQueryBuilder('t');

        $queryBuilder
            ->where('t.status IN (:statuses)')
            ->andWhere('t.rootCause IS NULL')
            ->andWhere('t.originalRootCause IS NOT NULL')
            ->setParameter('statuses', TechnicianOnCall::CLOSED_STATUSES)
        ;

        $technicianOnCalls = $queryBuilder->getQuery()->getResult();

        $output->writeln(\sprintf('Found %d TOCs to translate', \count($technicianOnCalls)));
        foreach ($technicianOnCalls as $technicianOnCall) {
            $this->translationManager->translateObjectProperties(
                $technicianOnCall,
                [
                    'originalSymptoms' => 'symptoms',
                    'originalRootCause' => 'rootCause',
                    'originalSolution' => 'solution',
                ]
            );

            $output->writeln(\sprintf('Translated %s', $technicianOnCall->getId()));
            $this->entityManager->persist($technicianOnCall);
        }

        $this->entityManager->flush();

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
