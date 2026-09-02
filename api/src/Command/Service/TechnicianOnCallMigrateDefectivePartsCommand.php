<?php

declare(strict_types=1);

namespace App\Command\Service;

use App\Entity\Service\TechnicianOnCallDefectivePart;
use App\Entity\Service\TechnicianOnCallPart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:technician_on_call:migrate:defective_parts')]
class TechnicianOnCallMigrateDefectivePartsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $technicianOnCallPartsRepository = $this->entityManager->getRepository(TechnicianOnCallPart::class);
        $technicianOnCallParts = $technicianOnCallPartsRepository->findBy(['defective' => true]);

        foreach ($technicianOnCallParts as $technicianOnCallPart) {
            $technicianOnCallDefectivePart = new TechnicianOnCallDefectivePart();

            $technicianOnCallDefectivePart->technicianOnCall = $technicianOnCallPart->technicianOnCall;
            $technicianOnCallDefectivePart->createdBy = $technicianOnCallPart->createdBy;
            $technicianOnCallDefectivePart->createdAt = $technicianOnCallPart->createdAt;
            $technicianOnCallDefectivePart->description = $technicianOnCallPart->description;
            $technicianOnCallDefectivePart->partNumber = $technicianOnCallPart->partNumber;
            $technicianOnCallDefectivePart->quantity = $technicianOnCallPart->quantity;

            $this->entityManager->persist($technicianOnCallDefectivePart);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
