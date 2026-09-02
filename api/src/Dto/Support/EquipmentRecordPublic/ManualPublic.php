<?php

declare(strict_types=1);

namespace App\Dto\Support\EquipmentRecordPublic;

final class ManualPublic
{
    public const string STATUS_RELEASED = 'RELEASED';

    public int $id;

    public string $createdAt;

    /** @var ManualDocumentPublic[] */
    public array $documents = [];
}
