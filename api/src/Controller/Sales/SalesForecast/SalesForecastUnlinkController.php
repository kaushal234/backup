<?php

declare(strict_types=1);

namespace App\Controller\Sales\SalesForecast;

use App\Entity\Sales\MasterSalesForecast;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SalesForecastUnlinkController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(SalesForecast $data)
    {
        $master = new MasterSalesForecast();
        $master->addSalesForecast($data);

        $this->entityManager->persist($master);
        $this->entityManager->flush();

        return $data;
    }
}
