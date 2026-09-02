<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\Parts\TOCSparePartsRequest;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:migrate:technician_on_call:spare_parts_request', description: 'Migrate link between TOC SPR and API TOC')]
class MigrateTechnicianOnCallSparePartsRequestCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $sparePartsRequestRepository = $this->entityManager->getRepository(TOCSparePartsRequest::class);
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);

        $technicianOnCallSparePartsRequests = $sparePartsRequestRepository->findBy(
            ['tocId' => explode(',', $this->tocIdList)],
        );

        $i = 0;
        foreach ($technicianOnCallSparePartsRequests as $sparePartsRequest) {
            $technicianOnCallLegacyId = $sparePartsRequest->tocId;

            $technicianOnCall = $technicianOnCallRepository->findOneBy(['legacyId' => $technicianOnCallLegacyId]);

            if (!$technicianOnCall) {
                $output->writeln(\sprintf('<error>Technician on call with legacy ID #%d not exist</error>', $technicianOnCallLegacyId));
                continue;
            }

            $sparePartsRequest->setTechnicianOnCall($technicianOnCall);
            $this->entityManager->persist($sparePartsRequest);
            $output->writeln(\sprintf('<info>SPR #%d updated</info>', $sparePartsRequest->getId()));

            ++$i;

            if ($i > 500) {
                $this->entityManager->flush();
                $i = 0;
            }
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
