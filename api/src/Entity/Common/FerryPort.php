<?php

declare(strict_types=1);

namespace App\Entity\Common;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\IATACode;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_IATA_CODE_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_IATA_CODE_WRITE')"),
    ],
    normalizationContext: ['groups' => ['iata_code_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['iata_code_write']],
)]
#[UniqueEntity(fields: ['code', 'cityName'], errorPath: 'code', ignoreNull: true)]
#[ApiFilter(OrderFilter::class, properties: ['code' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'code' => 'exact', 'cityCode3' => 'exact', 'cityName' => 'partial', 'country' => 'exact', 'type' => 'exact'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'airport_codes')]
class FerryPort extends IATACode
{
}
