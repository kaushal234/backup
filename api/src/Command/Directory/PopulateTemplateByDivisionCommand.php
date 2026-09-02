<?php

declare(strict_types=1);

namespace App\Command\Directory;

use App\Entity\Directory\Division;
use App\Entity\Directory\DivisionGroup;
use App\Entity\Directory\Position;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:position:populate_template')]
class PopulateTemplateByDivisionCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Populate template for each positions/division');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $divisions = $this->entityManager->getRepository(Division::class)->findAll();
        foreach ($this->entityManager->getRepository(Position::class)->findAll() as $position) {
            $groups = $position->getGroups();

            /** @var Division $division */
            foreach ($divisions as $division) {
                $divisionGroup = new DivisionGroup();
                $divisionGroup->division = $division;
                foreach ($groups as $group) {
                    $divisionGroup->addGroup($group);
                }

                $position->addDivisionGroup($divisionGroup);
                $this->entityManager->persist($position);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
