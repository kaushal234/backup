<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Service\TechnicianOnCall;

class ProductDemeritClaimManager
{
    public function __construct(
        private readonly ModLinkManager $modLinkManager,
        private readonly ModLogManager $modLogManager,
    ) {
    }

    public function getLogsForLinkTechnicianOnCall(int $technicianOnCallId): array
    {
        $linksModule = $this->modLinkManager->getLinks(module: 'PDC', type: TechnicianOnCall::MODULE_NAME, typeId: $technicianOnCallId);
        $linksType = $this->modLinkManager->getLinks(module: TechnicianOnCall::MODULE_NAME, moduleId: $technicianOnCallId, type: 'PDC');

        $links = [...$linksModule, ...$linksType];
        $productDemeritClaimIds = array_map(static function ($link) {
            return 'PDC' === $link['module'] ? $link['parent_id'] : $link['item'];
        }, $links);

        return $this->modLogManager->getModLogs($productDemeritClaimIds, 'PDC');
    }
}
