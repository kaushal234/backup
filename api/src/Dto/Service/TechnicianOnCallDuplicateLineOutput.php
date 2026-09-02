<?php

declare(strict_types=1);

namespace App\Dto\Service;

use Symfony\Component\Serializer\Attribute\Groups;

final class TechnicianOnCallDuplicateLineOutput
{
    #[Groups(['toc:read'])]
    public string $serialNumber;

    #[Groups(['toc:read'])]
    public ?int $technicianOnCallId = null;

    #[Groups(['toc:read'])]
    public ?string $technicianOnCallErrorMessage = null;

    #[Groups(['toc:read'])]
    public ?int $customerServiceRecordId = null;

    #[Groups(['toc:read'])]
    public ?string $customerServiceRecordErrorMessage = null;
}
