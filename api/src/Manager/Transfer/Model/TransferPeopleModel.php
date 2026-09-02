<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Model;

use App\Entity\Directory\People;

class TransferPeopleModel
{
    private ?People $target = null;

    private ?People $asmTarget = null;

    private ?People $serviceRepTarget = null;

    private ?People $partsRepTarget = null;

    private ?People $salesRepTarget = null;

    private ?People $supervisorTarget = null;

    public function getTarget(): ?People
    {
        return $this->target;
    }

    /**
     * @return $this
     */
    public function setTarget(?People $target)
    {
        $this->target = $target;

        return $this;
    }

    public function getAsmTarget(): ?People
    {
        return $this->asmTarget;
    }

    /**
     * @return TransferPeopleModel|null
     */
    public function setAsmTarget(?People $asmTarget)
    {
        $this->asmTarget = $asmTarget;

        return $this;
    }

    public function getServiceRepTarget(): ?People
    {
        return $this->serviceRepTarget;
    }

    /**
     * @return TransferPeopleModel|null
     */
    public function setServiceRepTarget(?People $serviceRepTarget)
    {
        $this->serviceRepTarget = $serviceRepTarget;

        return $this;
    }

    public function getPartsRepTarget(): ?People
    {
        return $this->partsRepTarget;
    }

    /**
     * @return TransferPeopleModel|null
     */
    public function setPartsRepTarget(?People $partsRepTarget)
    {
        $this->partsRepTarget = $partsRepTarget;

        return $this;
    }

    public function getSalesRepTarget(): ?People
    {
        return $this->salesRepTarget;
    }

    /**
     * @return TransferPeopleModel|null
     */
    public function setSalesRepTarget(?People $salesRepTarget)
    {
        $this->salesRepTarget = $salesRepTarget;

        return $this;
    }

    public function getSupervisorTarget(): ?People
    {
        return $this->supervisorTarget;
    }

    /**
     * @return TransferPeopleModel|null
     */
    public function setSupervisorTarget(?People $supervisorTarget)
    {
        $this->supervisorTarget = $supervisorTarget;

        return $this;
    }
}
