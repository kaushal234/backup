<?php

declare(strict_types=1);

namespace App\Command\HumanResources;

use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Manager\Directory\PeopleManager;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:annual_task', description: 'Create an annual task for MIS users')]
class CreateAnnualTaskForMISUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PeopleManager $peopleManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $departmentRepository = $this->entityManager->getRepository(Department::class);
        $misDepartment = $departmentRepository->findOneBy(['name' => 'Management of Information System']);

        if (null === $misDepartment) {
            $output->writeln('MIS Department not found.');

            return Command::FAILURE;
        }

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        foreach ($peopleRepository->findBy(['hidden' => false, 'disabled' => false, 'department' => $misDepartment]) as $misUser) {
            $this->peopleManager->createTaskForMISUserAndNotify($misUser);
            $output->writeln(\sprintf('Task created for %s', $misUser->getDisplayName()));
        }

        return Command::SUCCESS;
    }
}
