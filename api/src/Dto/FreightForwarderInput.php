<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\FreightForwarder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class FreightForwarderInput
{
    /**
     * @var Collection<FreightForwarder>
     */
    #[Assert\All([new Assert\Type(type: FreightForwarder::class)])]
    #[Assert\NotNull]
    private $freightForwarders;

    public function __construct()
    {
        $this->freightForwarders = new ArrayCollection();
    }

    /**
     * @return Collection<FreightForwarder>
     */
    public function getFreightForwarders()
    {
        return $this->freightForwarders;
    }

    public function addFreightForwarder(FreightForwarder $freightForwarders): self
    {
        $this->freightForwarders->add($freightForwarders);

        return $this;
    }

    public function removeFreightForwarder(FreightForwarder $freightForwarders): self
    {
        $this->freightForwarders->removeElement($freightForwarders);

        return $this;
    }
}
