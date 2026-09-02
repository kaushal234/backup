<?php

declare(strict_types=1);

namespace App\MessageHandler\Quality\Crab;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Quality\Crab;
use App\Message\Quality\Crab\CrabWrite;
use App\Notifier\Quality\Crab\CrabNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\EquipmentRecordManager;
use LegacyBundle\Manager\ModLogManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CrabWriteHandler
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly EquipmentRecordManager $equipmentRecordManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly CrabNotifier $notifier,
        private readonly ModLogManager $modLogManager,
    ) {
    }

    public function __invoke(CrabWrite $message): void
    {
        /** @var Crab $crab */
        $crab = $this->iriConverter->getResourceFromIri($message->getResourceIri());
        /** @var People $user */
        $user = $this->iriConverter->getResourceFromIri($message->getUserIri());

        if (null === $crab->equipmentRecord) {
            return;
        }

        $legacyEquipmentRecord = $this->equipmentRecordManager->findByLegacyId($crab->equipmentRecord->getLegacyId());
        $yellowTag = false;
        if (null !== $legacyEquipmentRecord['dgt_act'] && Crab::CLOSED !== $crab->status) {
            $yellowTag = true;
        }

        if (Request::METHOD_PUT === $message->getMethod() && !$yellowTag) {
            return;
        }

        if ($yellowTag) {
            $equipmentRecord = $crab->equipmentRecord;
            $equipmentRecord
                ->setYellowTagDate(new \DateTime())
                ->setGreenTagDate(null)
            ;

            $this->entityManager->persist($equipmentRecord);
            $this->entityManager->flush();
            $this->modLogManager->insertLog($crab->equipmentRecord->getLegacyId(), 'ER', \sprintf('update from new CRAB#%s', $crab->getId()), $user);
        }

        $this->notifier->sendWrite($crab, $user, $yellowTag);
    }
}
