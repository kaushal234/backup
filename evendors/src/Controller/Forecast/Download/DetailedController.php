<?php

declare(strict_types=1);

namespace App\Controller\Forecast\Download;

use App\Sdk\Downloader;
use App\Sdk\Resource\MaterialRequirementsPlanning;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forecast/download')]
final class DetailedController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/detailed/xlsx', name: 'forecast:download:detailed', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        return $this->downloader->downloadExcel(MaterialRequirementsPlanning::class, [], 'forecast-detailed');
    }
}
