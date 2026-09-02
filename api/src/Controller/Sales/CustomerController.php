<?php

declare(strict_types=1);

namespace App\Controller\Sales;

use App\Entity\Sales\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CustomerController extends AbstractController
{
    public function __invoke(Customer $customer)
    {
        return $this->topLevelParentCustomer($customer);
    }

    private function topLevelParentCustomer(Customer $customer): Customer
    {
        if (null !== $customer->getParentCustomer()) {
            return $this->topLevelParentCustomer($customer->getParentCustomer());
        }

        return $customer;
    }
}
