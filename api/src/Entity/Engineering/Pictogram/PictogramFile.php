<?php

declare(strict_types=1);

namespace App\Entity\Engineering\Pictogram;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/{id}/files',
            uriVariables: [
                'id' => new Link(toProperty: 'pictogram', fromClass: Pictogram::class),
            ],
        ),
        new Get(),
    ],
    routePrefix: 'engineering/pictograms',
)]
#[ORM\Entity]
#[ORM\Table(name: 'pictograms_files')]
#[App\Loggable(owner: 'pictogram', ownerRelation: 'files')]
#[ApiFilter(SearchFilter::class, properties: [
    'pictogram',
    'main',
])]
class PictogramFile extends File
{
    #[ORM\ManyToOne(targetEntity: Pictogram::class, inversedBy: 'files')]
    public ?Pictogram $pictogram = null;

    #[ORM\Column]
    public bool $main = false;
}
