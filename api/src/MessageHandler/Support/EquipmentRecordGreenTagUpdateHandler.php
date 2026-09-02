<?php

declare(strict_types=1);

namespace App\MessageHandler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Message\Support\EquipmentRecordGreenTagUpdate;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Notifier\Tasks\SequenceNotifier;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Model\Sequence;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EquipmentRecordGreenTagUpdateHandler
{
    public const SEQUENCE_LAST_DAY = 'odp.lategt.approval.gceo';
    public const SEQUENCE_LAST_DAY_1 = 'odp.lategt.approval.gcoo';
    public const SEQUENCE_LAST_DAY_2 = 'odp.lategt.approval.rceo';

    public function __construct(
        private readonly SequenceManager $sequenceManager,
        private readonly PeopleRepository $peopleRepository,
        private readonly LocationRepository $locationRepository,
        private readonly SequenceNotifier $sequenceNotifier,
        private readonly EquipmentRecordNotifier $equipmentRecordNotifier,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(EquipmentRecordGreenTagUpdate $message): void
    {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $this->iriConverter->getResourceFromIri($message->getEquipmentRecordIri());
        /** @var People $user */
        $user = $this->iriConverter->getResourceFromIri($message->getUserIri());

        $lastDayOfCurrentMonth = new \DateTime('last day of this month');
        $greenTagDate = \DateTime::createFromInterface($equipmentRecord->getGreenTagDate());
        $interval = $lastDayOfCurrentMonth->diff($greenTagDate->setTime(0, 0))->days;

        if (
            0 <= $interval && $interval < 3
            && !$equipmentRecord->isLight()
            && null !== $equipmentRecord->orderFactory
            && $equipmentRecord->getGreenTagDate() === $equipmentRecord->getFirstGreenTagDate()
        ) {
            $sequence = new Sequence();
            $sequence
                ->setCloseParams(['dgt_act' => $equipmentRecord->getGreenTagDate()->format('Y-m-d')])
                ->setAssignee($user)
                ->setAssignor($user)
                ->setParentId($equipmentRecord->getLegacyId())
                ->setLocation($equipmentRecord->getManufacturerLocation())
                ->setModule('ER')
                ->setDescription(\sprintf("Late GT Request for Unit <a href=\'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$equipmentRecord->getLegacyId()}'>%s</a>
        <br>
        Manufacturer : %s
        Sales Organisation : %s
        Model : %s
        Buyer : %s
        Actual GT Date : %s
        Factory EXW Promise : %s
        Estimated GT Date : %s",
                    $equipmentRecord->getSerialNumber(),
                    $equipmentRecord->getManufacturerLocation()?->getName() ?? '',
                    $equipmentRecord->getSalesOrganisation()?->getName() ?? '',
                    $equipmentRecord->getModel() ?? '',
                    $equipmentRecord->getBuyer()?->getName() ?? '',
                    $equipmentRecord->getGreenTagDate()?->format('Y-m-d') ?? '',
                    $equipmentRecord->orderFactory->factoryPromisedDeliveryDate->format('Y-m-d'),
                    $equipmentRecord->getEstimatedGreenTagDate()?->format('Y-m-d') ?? '',
                ))
            ;

            $sam = $this->peopleRepository->findGroupsMembers(['role_SAM', 'role_CEO'], $equipmentRecord->getSalesOrganisation());
            $ccFromFactory = $this->peopleRepository->findGroupsMembers(['role_COO', 'role_QAM', 'role_PSM', 'role_PSE', 'role_PM', 'role_PS'], $equipmentRecord->getManufacturerLocation());
            foreach (array_merge($sam, $ccFromFactory) as $people) {
                $sequence->addCc($people);
            }

            switch ($interval) {
                case 0:
                    $gcoo = $this->peopleRepository->findGroupMembers('role_GCOO', $this->locationRepository->findOneBy(['erp' => 900]));
                    foreach ($gcoo as $people) {
                        $sequence->addCc($people);
                    }
                    $sequence->setTemplateName(self::SEQUENCE_LAST_DAY);
                    break;
                case 1:
                    $rceo = $this->peopleRepository->findGroupMembers('role_RCEO', $equipmentRecord->getSalesOrganisation());
                    foreach ($rceo as $people) {
                        $sequence->addCc($people);
                    }
                    $sequence->setTemplateName(self::SEQUENCE_LAST_DAY_1);
                    break;
                case 2:
                    $sequence->setTemplateName(self::SEQUENCE_LAST_DAY_2);
                    break;
                default:
            }

            $sequenceId = $this->sequenceManager->insert($sequence);
            $this->sequenceNotifier->sendEmail($sequence, 'manufacturing.equipment_record.sequence.subject', 'Emails/Task/new_sequence.html.twig', ['%serialNumber%' => $equipmentRecord->getSerialNumber(), '%sequenceId%' => $sequenceId]);
        }
        $this->equipmentRecordNotifier->sendGreenTagDateUpdateEmail($equipmentRecord, $message->getPreviousGreenTagDate());
    }
}
