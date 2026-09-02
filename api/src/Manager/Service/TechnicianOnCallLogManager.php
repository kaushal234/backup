<?php

declare(strict_types=1);

namespace App\Manager\Service;

use App\Doctrine\Change;
use App\Entity\Service\TechnicianOnCall;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TechnicianOnCallLogManager
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function creationComment(Change $change): string
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $change->getEntity();

        return $this->translator->trans('toc.comment.creation', [
            '%created_by%' => \sprintf('%s %s', ucfirst($technicianOnCall->createdBy->getFirstname()), ucfirst($technicianOnCall->createdBy->getLastname())),
            '%creation_date%' => $technicianOnCall->createdAt->format('Y-m-d H:i:s'),
        ], 'technician_on_call');
    }

    public function logToComment(Change $change): ?string
    {
        $commentParts = [];

        foreach ($change->getChangeSet() as $key => $changeSet) {
            if (null === $changeSet[1]) {
                continue;
            }

            if (!\in_array($key, ['title', 'description', 'status'], true)) {
                continue;
            }

            $commentParts[] = $this->translator->trans(\sprintf('toc.comment.%s', $key), ['%old%' => $changeSet[0] ?? 'empty', '%value%' => $changeSet[1]], 'technician_on_call');
        }

        return [] !== $commentParts ? implode("\n", $commentParts) : null;
    }
}
