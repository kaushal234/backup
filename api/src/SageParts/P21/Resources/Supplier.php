<?php

declare(strict_types=1);

namespace App\SageParts\P21\Resources;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ExternalERP\Filter\ContainsFilter;
use App\ExternalERP\Filter\EqualsFilter;
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
    normalizationContext: ['groups' => ['supplier']],
    denormalizationContext: ['groups' => ['supplier']],
)]
#[ApiFilter(ContainsFilter::class, properties: ['name'])]
#[ApiFilter(EqualsFilter::class, properties: ['code'])]
class Supplier
{
    #[Groups('supplier')]
    public string $code;

    #[Groups('supplier')]
    public string $name;

    #[Groups('supplier')]
    public string $buyerEmail;
}
