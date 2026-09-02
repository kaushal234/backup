<?php

declare(strict_types=1);

namespace App\ION\Manager\MasterData\EnterpriseModel\EnterpriseStructure;

use App\Entity\Directory\Location;
use App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure\Site;
use App\Repository\Directory\LocationRepository;

class SiteToLocationConverter
{
    private readonly LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function getLocationFromSite(Site $site): ?Location
    {
        return $this->locationRepository->findOneBy(['erp' => $site->siteID]);
    }
}
