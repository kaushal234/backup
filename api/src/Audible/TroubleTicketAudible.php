<?php

declare(strict_types=1);

namespace App\Audible;

use App\Entity\MIS\TroubleTicket\TroubleTicket;

class TroubleTicketAudible implements AudibleInterface
{
    public function supports(string $type): bool
    {
        return 'trouble_ticket' === $type;
    }

    public function getClass(): string
    {
        return TroubleTicket::class;
    }

    public function getAudibleProperties(): array
    {
        return ['status'];
    }
}
