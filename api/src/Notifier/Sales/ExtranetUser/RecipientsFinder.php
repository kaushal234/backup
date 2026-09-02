<?php

declare(strict_types=1);

namespace App\Notifier\Sales\ExtranetUser;

use App\Entity\Module\Module;
use App\Repository\Module\ModuleRepository;

class RecipientsFinder
{
    private readonly ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    public function findRecipients(): array
    {
        $recipients = [];
        foreach (['XU', 'CRT'] as $acronym) {
            $module = $this->moduleRepository->findByName($acronym);
            if (!$module instanceof Module || null === $module->getOperationalOwner() || 'DISABLED' === $module->status) {
                continue;
            }
            $recipients[] = $module->getOperationalOwner();
        }

        return $recipients;
    }
}
