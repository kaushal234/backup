<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

class LegacyFile
{
    #[Groups(['file', 'file:light'])]
    public int $id;

    #[Groups(['file', 'file:light'])]
    public string $filePath;

    #[Groups(['file'])]
    public ?User $poster = null;

    #[Groups(['file', 'file:light'])]
    public \DateTimeInterface $createdAt;

    #[Groups(['file'])]
    public ?string $description = null;
}
