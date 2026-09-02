<?php

declare(strict_types=1);

namespace LegacyBundle\Dto;

use App\Entity\Parts\SparePartsRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class TOC
{
    #[Groups(['legacy:toc'])]
    public string $status;

    /** @var Collection<SparePartsRequest> */
    #[Groups(['legacy:toc'])]
    public Collection $sparePartsRequest;

    #[Groups(['legacy:toc'])]
    private string $description;

    public function __construct()
    {
        $this->sparePartsRequest = new ArrayCollection();
    }

    public function setDescription(string $description): void
    {
        $this->description = mb_convert_encoding($description, 'UTF-8', mb_list_encodings());
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
