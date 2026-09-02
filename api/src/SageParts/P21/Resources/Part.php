<?php

declare(strict_types=1);

namespace App\SageParts\P21\Resources;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ExternalERP\Filter\ContainsFilter;
use App\SageParts\P21\DataProvider\CollectionDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: CollectionDataProvider::class
        ),
        new Get(controller: NotFoundAction::class),
    ],
    routePrefix: 'sageparts',
    normalizationContext: ['groups' => ['part']],
    denormalizationContext: ['groups' => ['part']],
)]
#[ApiFilter(ContainsFilter::class, properties: ['item'])]
class Part
{
    #[Groups('part')]
    public string $item;

    #[Groups('part')]
    public string $itemDescription;

    #[Groups('part')]
    public string $unitOfMeasure;
}
