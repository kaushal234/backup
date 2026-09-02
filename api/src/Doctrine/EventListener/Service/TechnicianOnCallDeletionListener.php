<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener\Service;

use App\Entity\Service\TechnicianOnCall;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Manager\ModLinkManager;
use LegacyBundle\Repository\WarrantyClaimRepository;

#[AsDoctrineListener(Events::postRemove)]
class TechnicianOnCallDeletionListener
{
    public function __construct(
        private readonly ModLinkManager $modLinkManager,
        private readonly Connection $legacyConnection,
        private readonly WarrantyClaimRepository $warrantyClaimRepository,
    ) {
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $technicianOnCall = $args->getObject();
        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return;
        }

        $technicianOnCallLegacyId = $technicianOnCall->getLegacyId();
        if (null === $technicianOnCallLegacyId) {
            return;
        }

        $wcLinks = $this->modLinkManager->getLinks(module: TechnicianOnCall::MODULE_NAME, moduleId: $technicianOnCallLegacyId, type: 'WC');
        foreach ($wcLinks as $link) {
            $warrantyClaim = $this->warrantyClaimRepository->find((int) $link['item']);
            if (null === $warrantyClaim || WarrantyClaim::PENDING !== $warrantyClaim->status) {
                continue;
            }

            $this->legacyConnection->delete('warranty', ['id' => (int) $link['item']]);
            $this->legacyConnection->delete('mod_links', ['id' => (int) $link['id']]);
        }
    }
}
