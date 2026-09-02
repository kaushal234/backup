<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\TechnicianOnCallHourMeterTransaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:migrate:technician_on_call:hour_meter', description: 'Migrate link between TOC Hour Meter and API TOC')]
class MigrateTechnicianOnCallHourMeterTransactionsCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $hourMeterRepository = $this->entityManager->getRepository(TechnicianOnCallHourMeterTransaction::class);
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);

        $hourMeters = $hourMeterRepository->findBy(
            ['tocLegacyId' => explode(',', $this->tocIdList)],
        );

        $i = 0;
        foreach ($hourMeters as $hourMeter) {
            $technicianOnCallLegacyId = $hourMeter->tocLegacyId;

            $technicianOnCall = $technicianOnCallRepository->findOneBy(['legacyId' => $technicianOnCallLegacyId]);

            if (!$technicianOnCall) {
                $output->writeln(\sprintf('<error>Technician on call with legacy ID #%d not exist</error>', $technicianOnCallLegacyId));
                continue;
            }

            $hourMeter->setTechnicianOnCall($technicianOnCall);
            $this->entityManager->persist($hourMeter);
            $output->writeln(\sprintf('<info>Hourmeter #%d updated</info>', $hourMeter->getId()));

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
