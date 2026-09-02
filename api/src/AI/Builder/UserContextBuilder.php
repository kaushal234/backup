<?php

declare(strict_types=1);

namespace App\AI\Builder;

use App\Entity\Directory\People;

final readonly class UserContextBuilder
{
    public function build(?People $user): ?string
    {
        if (null === $user) {
            return null;
        }

        $lines = ['The user you are talking to:'];
        $lines[] = \sprintf('- Name: %s', $user->getDisplayName());

        if (null !== $user->getJobTitle()) {
            $lines[] = \sprintf('- Job title: %s', $user->getJobTitle());
        }
        if (null !== $user->getPosition()) {
            $lines[] = \sprintf('- Position: %s', (string) $user->getPosition());
        }
        if (null !== $user->getDepartment()) {
            $lines[] = \sprintf('- Department: %s', (string) $user->getDepartment());
        }
        if (null !== $user->getBusinessUnit()) {
            $lines[] = \sprintf('- Business unit: %s', (string) $user->getBusinessUnit());
        }
        if (null !== $user->getLocale()) {
            $lines[] = \sprintf('- Locale: %s (prefer this language unless the user writes in another)', $user->getLocale());
        }

        return implode("\n", $lines);
    }
}
