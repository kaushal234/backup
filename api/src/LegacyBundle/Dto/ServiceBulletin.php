<?php

declare(strict_types=1);

namespace LegacyBundle\Dto;

use App\Entity\Parts\SparePartsRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class ServiceBulletin
{
    #[Groups(['legacy:service_bulletin'])]
    public string $status;

    #[Groups(['legacy:service_bulletin'])]
    public string $category;

    #[Groups(['legacy:service_bulletin'])]
    public string $title;

    /** @var Collection<SparePartsRequest> */
    #[Groups(['legacy:service_bulletin'])]
    public Collection $sparePartsRequest;

    public function __construct()
    {
        $this->sparePartsRequest = new ArrayCollection();
    }
}
