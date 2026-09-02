<?php

declare(strict_types=1);

namespace Shared\Provider\Manufacturing\JobShop;

use Shared\Provider\AbstractProvider;
use Shared\Ressources\Manufacturing\JobShop\JobShopBillOfMaterialBenchmark;

class JobShopBillOfMaterialBenchmarkProvider extends AbstractProvider
{
    public function getItem(int $erp, string $item, \DateTime $dateTime, array $otherERPs = [], string $project = null): JobShopBillOfMaterialBenchmark
    {
        $jsbom = $this->client->get(
            sprintf(
                '/ion/bill-of-materials/purchasing_benchmarks/site=%d;project=%s;product=%s?otherSites=%s',
                $erp,
                $project,
                $item,
                !empty($otherERPs) ? implode('|', $otherERPs) : 0
            ),
            [
                'query' => [
                    'date' => $dateTime->format(\DateTimeInterface::ATOM),
                ]
            ]
        );

        return $this->serializer->denormalize($jsbom, JobShopBillOfMaterialBenchmark::class);
    }
}