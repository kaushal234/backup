<?php

declare(strict_types=1);

namespace App\Command\Directory;

use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:people:legal-entity:initiate')]
class InitiatePeopleLegalEntityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Initializes legalEntity property (if null) for every People with their current BusinessUnit');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        $i = 0;

        /** @var People $people */
        foreach ($peopleRepository->findAll() as $people) {
            if (null === $people->getLegalEntity()) {
                $people->setLegalEntity($people->getBusinessUnit());
                ++$i;
            }
            if (($i % 1000) === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
