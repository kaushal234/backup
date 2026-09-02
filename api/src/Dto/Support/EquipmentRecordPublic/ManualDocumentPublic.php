<?php

declare(strict_types=1);

namespace App\Dto\Support\EquipmentRecordPublic;

class ManualDocumentPublic
{
    public const array PUBLIC_CATEGORY_NAMES = [
        'Chapter 0',
        'Chapter 1',
    ];

    public int $id;

    public ?string $description = null;

    public ?string $categoryName = null;

    public ?int $fileId = null;

    public ?string $extension = null;
}
