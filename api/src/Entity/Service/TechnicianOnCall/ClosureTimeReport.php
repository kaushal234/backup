<?php

declare(strict_types=1);

namespace App\Entity\Service\TechnicianOnCall;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProvider\Service\ClosureTimeReportDataProvider;
use App\Filter\Service\ClosureTimeDateFilter;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: ClosureTimeReportDataProvider::class
        ),
    ],
    routePrefix: '/service/technician-on-call',
    filters: [ClosureTimeDateFilter::class]
)]
class ClosureTimeReport
{
    public string $delay;

    public int $numberOfTechnicianOnCalls;

    public int|float $percentRemoteSolved;

    public int|float $deltaWithYear;
}
