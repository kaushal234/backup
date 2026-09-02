<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders\Statistic;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    #[Groups(['purchase_order_statistic'])]
    public string $code;

    #[Groups(['purchase_order_statistic'])]
    #[Assert\Email]
    public string $emailAddress;
}
