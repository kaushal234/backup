<?php

declare(strict_types=1);

namespace App\MessageHandler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use App\Factory\Support\ManualFactory;
use App\Message\Support\ManualDuplication;
use App\Notifier\Support\ManualDuplicationNotifier;
use App\Util\Iri;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ManualDuplicationHandler
{
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;
    private readonly ManualFactory $manualFactory;
    private readonly ManualDuplicationNotifier $notifier;
    private readonly LoggerInterface $logger;

    public function __construct(
        IriConverterInterface $iriConverter,
        EntityManagerInterface $entityManager,
        ManualFactory $manualFactory,
        ManualDuplicationNotifier $notifier,
        LoggerInterface $logger
    ) {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
        $this->manualFactory = $manualFactory;
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    public function __invoke(ManualDuplication $message): void
    {
        /** @var Manual $manual */
        $manual = $this->entityManager->getRepository(Manual::class)->find(Iri::id($message->getResourceIri()));
        $errors = [];
        $duplicatedManuals[] = ['id' => $manual->getId()];
        /** @var People $user */
        $user = $this->iriConverter->getResourceFromIri($message->getUserIri());

        foreach ($message->getSecondaryEquipmentRecords() as $equipmentRecordIri) {
            try {
                /** @var EquipmentRecord $secondaryEquipmentRecord */
                $secondaryEquipmentRecord = $this->iriConverter->getResourceFromIri($equipmentRecordIri);
                $secondaryEquipmentRecord->setPublishable(true);

                $clone = $this->manualFactory->cloneManual($manual, $secondaryEquipmentRecord);

                foreach ($message->getSchematicsSerialsIri() as $equipmentSerialIri) {
                    /** @var EquipmentSerial $equipmentSerial */
                    $equipmentSerial = $this->iriConverter->getResourceFromIri($equipmentSerialIri);
                    $clonedSerial = clone $equipmentSerial;
                    $clonedSerial->equipmentRecord = $secondaryEquipmentRecord;

                    $this->entityManager->getRepository(EquipmentSerial::class)->createOrFindExistingSerial($clonedSerial);
                }
                $clone->equipmentSerial = null;
                $this->entityManager->persist($clone);
                $this->entityManager->flush();

                $equipmentSerial = new EquipmentSerial();
                $equipmentSerial->serial = (string) $clone->getLegacyId();
                $equipmentSerial->component = $this->entityManager->getRepository(Component::class)->findOneBy(['name' => Component::MANUAL]);
                $equipmentSerial->equipmentRecord = $secondaryEquipmentRecord;
                $clone->equipmentSerial = $equipmentSerial;

                $this->entityManager->persist($equipmentSerial);
                $this->entityManager->persist($clone);

                $this->entityManager->flush();

                $duplicatedManuals[] = ['id' => $clone->getId()];
            } catch (\Exception $exception) {
                $errors[] = \sprintf('Error when trying to duplicate manual with id %d.', $manual->getId());
                $this->logger->critical(\sprintf('Error when trying to duplicate manual with id %d.', $manual->getId()), [
                    'user' => $user->getEmail(),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $this->notifier->sendEmail($user->getEmail(), $manual, $errors, $duplicatedManuals);
    }
}
