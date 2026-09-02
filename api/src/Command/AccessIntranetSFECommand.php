<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Acl;
use App\Entity\Directory\ContractType;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:sfe:access', description: 'Give ACL_AUTH_INTRANET for all SFE that need it')]
class AccessIntranetSFECommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $positionRepository = $this->entityManager->getRepository(Position::class);
        $groupRepository = $this->entityManager->getRepository(Group::class);

        $shopFloorPosition = $positionRepository->findOneBy(['code' => 'SFE']);
        $aclAuthIntranet = $groupRepository->findOneBy(['name' => 'ACL_AUTH_INTRANET']);

        $i = 0;
        foreach ($peopleRepository->findBy(['position' => $shopFloorPosition, 'disabled' => false, 'hidden' => false]) as $shopFloorEmployee) {
            if (ContractType::TEMP_AND_CONSULTANTS === $shopFloorEmployee->getContractType()->name) {
                continue;
            }

            $acl = (new Acl())
                ->setUser($shopFloorEmployee)
                ->setLocation($shopFloorEmployee->getBusinessUnit()->getLocation())
                ->setGroup($aclAuthIntranet)
            ;

            $this->entityManager->persist($acl);
            ++$i;
            $output->writeln(\sprintf('ACL_AUTH_INTRANET given to %s', $shopFloorEmployee->getDisplayName()));
        }

        $output->writeln(\sprintf('Access given to %d employees.', $i));
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
