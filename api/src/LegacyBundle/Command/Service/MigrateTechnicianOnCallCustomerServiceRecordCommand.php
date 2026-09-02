<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Doctrine\EventListener\EntityChangeListener;
use App\Doctrine\Utils\ListenerManager;
use App\Entity\Activity\Comment;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Doctrine\EventListener\PersistenceSubscriber;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:migrate:technician_on_call:customer_service_record', description: 'Migrate link between TOC CSR and API TOC')]
class MigrateTechnicianOnCallCustomerServiceRecordCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ListenerManager $listenerManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);
        $this->listenerManager->removeListener($this->entityManager, [EntityChangeListener::class, PersistenceSubscriber::class]);

        $customerServiceRecordRepository = $this->entityManager->getRepository(TechnicianOnCallCustomerServiceRecord::class);
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);

        $technicianOnCallCustomerServiceRecord = $customerServiceRecordRepository->findBy(
            ['tocLegacyId' => explode(',', $this->tocIdList)],
        );

        $i = 0;
        foreach ($technicianOnCallCustomerServiceRecord as $customerServiceRecord) {
            $technicianOnCallLegacyId = $customerServiceRecord->tocLegacyId;

            $technicianOnCall = $technicianOnCallRepository->findOneBy(['legacyId' => $technicianOnCallLegacyId]);

            if (!$technicianOnCall) {
                $output->writeln(\sprintf('<error>Technician on call with legacy ID #%d not exist</error>', $technicianOnCallLegacyId));
                continue;
            }

            $customerServiceRecord->setTechnicianOnCall($technicianOnCall);
            $this->entityManager->persist($customerServiceRecord);
            $output->writeln(\sprintf('<info>CSR #%d updated with TOC #%d</info>', $customerServiceRecord->getId(), $technicianOnCall->getId()));

            ++$i;

            if ($i > 500) {
                $this->entityManager->flush();
                $i = 0;
            }
        }
        $this->entityManager->flush();
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        return Command::SUCCESS;
    }
}
