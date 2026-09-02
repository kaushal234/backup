<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Directory\Phone;
use Doctrine\Common\Collections\Collection;

interface PhoneInterface
{
    /** @return Collection<Phone> */
    public function getPhones();
}
