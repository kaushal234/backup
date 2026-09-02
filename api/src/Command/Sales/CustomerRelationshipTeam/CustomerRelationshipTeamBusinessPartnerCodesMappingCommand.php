<?php

declare(strict_types=1);

namespace App\Command\Sales\CustomerRelationshipTeam;

use App\Command\Sales\Order\SalesOrdersBaanBpCodeMappingCommand;
use App\Entity\Sales\CustomerRelationshipTeam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'crt:bp-code:map')]
class CustomerRelationshipTeamBusinessPartnerCodesMappingCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('customerBusinessPartnerCode property for CRTs');
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $crtRepository = $this->entityManager->getRepository(CustomerRelationshipTeam::class);

        $updatedCrtCount = 0;
        foreach ($crtRepository->findAll() as $crt) {
            $key = $crt->getErpLocation() ? $crt->getCustomer()?->getId() : null;
            if ($key && \array_key_exists($key, SalesOrdersBaanBpCodeMappingCommand::CUNO_BP_CODES_MAPPING)) {
                ++$updatedCrtCount;
                $crt->setCustomerBusinessPartnerCode(SalesOrdersBaanBpCodeMappingCommand::CUNO_BP_CODES_MAPPING[$key]);
                $this->entityManager->persist($crt);
            }
        }

        $this->entityManager->flush();
        $output->writeln(\sprintf('Assigned Business Partner Codes to %s CRTs', $updatedCrtCount));

        return Command::SUCCESS;
    }
}
