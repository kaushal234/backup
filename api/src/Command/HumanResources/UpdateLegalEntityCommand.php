<?php

declare(strict_types=1);

namespace App\Command\HumanResources;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:legal-entity')]
class UpdateLegalEntityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $businessUnitRepository = $this->entityManager->getRepository(BusinessUnit::class);

        $tldECATtBusinessUnit = $businessUnitRepository->findOneBy(['name' => 'TLD EUR']);

        $i = 0;
        foreach ($peopleRepository->findAll() as $people) {
            if (null === $people->getBusinessUnit() || null !== $people->getBusinessUnit() && !\in_array($people->getBusinessUnit()->getName(), ['TLD MTL', 'TLD STL', 'TLD ECAT'], true)) {
                continue;
            }

            $people->setLegalEntity($tldECATtBusinessUnit);
            $this->entityManager->persist($people);
            ++$i;
        }

        $this->entityManager->flush();
        $output->writeln(\sprintf('%d people updated.', $i));

        return Command::SUCCESS;
    }
}
