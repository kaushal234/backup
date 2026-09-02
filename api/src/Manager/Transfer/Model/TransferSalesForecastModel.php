<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Model;

use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Validator\Constraints\Location as ValidLocation;
use Symfony\Component\Validator\Constraints as Assert;

class TransferSalesForecastModel
{
    #[Assert\NotNull]
    #[ValidLocation(sso: true)]
    private Location $sso;

    private ?Country $country = null;

    private ?People $asmSource = null;

    #[Assert\NotNull]
    private People $asmTarget;

    private ?Customer $buyer = null;

    private ?Customer $endUser = null;

    public function getSso(): Location
    {
        return $this->sso;
    }

    public function setSso(Location $sso): void
    {
        $this->sso = $sso;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function getAsmSource(): ?People
    {
        return $this->asmSource;
    }

    public function setAsmSource(?People $asmSource): void
    {
        $this->asmSource = $asmSource;
    }

    public function getAsmTarget(): People
    {
        return $this->asmTarget;
    }

    public function setAsmTarget(People $asmTarget): void
    {
        $this->asmTarget = $asmTarget;
    }

    public function getBuyer(): ?Customer
    {
        return $this->buyer;
    }

    public function setBuyer(?Customer $buyer): void
    {
        $this->buyer = $buyer;
    }

    public function getEndUser(): ?Customer
    {
        return $this->endUser;
    }

    public function setEndUser(?Customer $endUser): void
    {
        $this->endUser = $endUser;
    }
}
