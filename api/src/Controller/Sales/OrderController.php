<?php

declare(strict_types=1);

namespace App\Controller\Sales;

use App\Entity\Sales\Order;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SalesOrderLineManager;

class OrderController
{
    private readonly EntityManagerInterface $entityManager;
    private readonly SalesOrderLineManager $salesOrderLineManager;

    public function __construct(EntityManagerInterface $entityManager, SalesOrderLineManager $salesOrderLineManager)
    {
        $this->entityManager = $entityManager;
        $this->salesOrderLineManager = $salesOrderLineManager;
    }

    public function __invoke(Order $data): Order
    {
        $duplicata = (clone $data)->reset();
        $this->entityManager->refresh($data);

        $this->entityManager->persist($duplicata);
        $this->entityManager->flush();

        $this->salesOrderLineManager->duplicateSalesOrderLines($data, $duplicata);

        return $duplicata;
    }
}
