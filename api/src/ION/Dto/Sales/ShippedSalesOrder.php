<?php

declare(strict_types=1);

namespace App\ION\Dto\Sales;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProcessor\Sales\ShippedSalesOrderDataProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            openapi: true,
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_SHIPPED_SALES_ORDERS_NOTIFY')",
            processor: ShippedSalesOrderDataProcessor::class,
        ),
        new Get(requirements: ['id' => '.*'], controller: NotFoundAction::class, output: false, read: false),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['sales_order']],
    denormalizationContext: ['groups' => ['sales_order:write']]
)]

class ShippedSalesOrder
{
    #[Groups(['sales_order', 'sales_order:write'])]
    #[Assert\NotNull]
    #[ApiProperty(identifier: true)]
    public string $salesOrderNumber;

    public array $updatedSparePartsRequests = [];

    #[Groups(['sales_order'])]
    public function getMessage(): ?string
    {
        if ([] === $this->updatedSparePartsRequests) {
            return 'No SPR was updated';
        }

        return \sprintf('The following SPR were updated: %s', implode(', ', $this->updatedSparePartsRequests));
    }
}
