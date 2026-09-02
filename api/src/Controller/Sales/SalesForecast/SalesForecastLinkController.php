<?php

declare(strict_types=1);

namespace App\Controller\Sales\SalesForecast;

use App\Entity\Sales\MasterSalesForecast;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SalesForecastLinkController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(
        #[MapEntity(id: 'id')] SalesForecast $data,
        #[MapEntity(id: 'to_id')] SalesForecast $toSalesForecast
    ) {
        $masterSalesForecast = $toSalesForecast->getMasterSalesForecast();
        if (!$masterSalesForecast instanceof MasterSalesForecast) {
            throw new UnprocessableEntityHttpException();
        }

        $masterSalesForecast->addSalesForecast($data);

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
