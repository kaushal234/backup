<?php

declare(strict_types=1);

namespace App\Controller\Support\EquipmentRecord;

use App\Entity\EquipmentRecord;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use App\Link\Manager\Support\EquipmentRecordManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class EquipmentRecordLinkSynchronizationController
{
    public function __construct(
        private EquipmentRecordManager $manager,
    ) {
    }

    public function __invoke(EquipmentRecord $equipmentRecord)
    {
        if ($equipmentRecord->getSerials()->filter(static fn (EquipmentSerial $serial) => Component::OBU_LINK === $serial->component->name)->isEmpty()) {
            throw new BadRequestHttpException(\sprintf('This Equipment Record has no %s component, then it\'s not synchronizable with Link', Component::OBU_LINK));
        }

        try {
            $this->manager->synchronizeEquipmentRecord([$equipmentRecord]);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException(\sprintf('Equipment Record could not be synchronized to Link. %s', $exception->getMessage()));
        }

        return $equipmentRecord;
    }
}
