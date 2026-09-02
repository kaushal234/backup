<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Model;

use App\Entity\Sales\Customer;

class TransferCustomerModel
{
    private Customer $target;

    public function getTarget(): Customer
    {
        return $this->target;
    }

    /**
     * @return $this
     */
    public function setTarget(Customer $target)
    {
        $this->target = $target;

        return $this;
    }
}
