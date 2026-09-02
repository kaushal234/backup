<?php

declare(strict_types=1);

namespace App\Command\Directory;

use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:people:add_acl', description: 'Clean up ACL by adding all standard permissions by position')]
class AddStandardAclByPositionCommand extends Command
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var People $people */
        foreach ($this->peopleRepository->findBy(['disabled' => false, 'hidden' => false]) as $people) {
            if (BusinessUnit::ALVEST_ARABIA_EQUIPMENT_SERVICES === $people->getBusinessUnit()->getLocation()->getName()) {
                continue;
            }
            foreach ($people->getPosition()->getDivisionGroups() as $divisionGroup) {
                if ($divisionGroup->division !== $people->getBusinessUnit()->getRegion()->getSubDivision()->division) {
                    continue;
                }

                foreach ($divisionGroup->getGroups() as $group) {
                    $aclExists = false;
                    foreach ($people->getAcls() as $acl) {
                        if ($acl->getGroup() === $group) {
                            $aclExists = true;
                        }
                    }

                    if (!$aclExists) {
                        $aclToCreate = (new Acl())
                            ->setGroup($group)
                            ->setUser($people)
                            ->setLocation($people->getBusinessUnit()->getLocation())
                        ;

                        $this->entityManager->persist($aclToCreate);
                        $output->writeln(\sprintf('Group %s added on %s with BU %s', $group->getName(), $people->getDisplayName(), $people->getBusinessUnit()->getLocation()->getName()));
                    }
                }
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
