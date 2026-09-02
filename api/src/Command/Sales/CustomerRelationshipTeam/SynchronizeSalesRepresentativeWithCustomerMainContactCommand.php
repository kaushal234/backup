<?php

declare(strict_types=1);

namespace App\Command\Sales\CustomerRelationshipTeam;

use App\Entity\Sales\CustomerRelationshipTeam;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:synchronize:crt-sales-representative')]
class SynchronizeSalesRepresentativeWithCustomerMainContactCommand extends Command
{
    private readonly CustomerRelationshipTeamRepository $customerRelationshipTeamRepository;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, CustomerRelationshipTeamRepository $customerRelationshipTeamRepository)
    {
        parent::__construct();
        $this->setDescription('Synchronize the CRT sales representative with the eCustomer main contact');
        $this->customerRelationshipTeamRepository = $customerRelationshipTeamRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $customerRelationshipTeams = $this->customerRelationshipTeamRepository->findWithSalesRepresentativeNotSynchronizeWithCustomer();

        /** @var CustomerRelationshipTeam $customerRelationshipTeam */
        foreach ($customerRelationshipTeams as $customerRelationshipTeam) {
            $previousSaleRepresentative = $customerRelationshipTeam->getSalesRepresentative();
            $newSaleRepresentative = $customerRelationshipTeam->getCustomer()->getMainSalesRepresentative()->asm;
            $customerRelationshipTeam->setSalesRepresentative($newSaleRepresentative);

            $this->entityManager->persist($customerRelationshipTeam);
            $output->writeln(\sprintf(
                '<info>[CRT #%d] sales representative changed from %s (%d) to %s (%d)</info>',
                $customerRelationshipTeam->getID(),
                $previousSaleRepresentative->getEmail(),
                $previousSaleRepresentative->getId(),
                $newSaleRepresentative->getEmail(),
                $newSaleRepresentative->getId()
            ));
        }

        $this->entityManager->flush();
        $output->writeln(\sprintf('<info>%d sales representative on customer relationship team have been synchronized with customer main sales representative</info>', \count($customerRelationshipTeams)));

        return 0;
    }
}
