<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\Sales;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Sales\SalesOrder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SalesOrderValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        try {
            $metadata = $this->resourceMetadataFactory->create(SalesOrder::class);

            /** @var SalesOrder|null $salesOrder */
            $salesOrder = $this->itemDataProvider->provide($metadata->getOperation(), ['salesOrder' => $value]);
        } catch (SoapException $exception) {
            // do nothing
        }

        if (null !== ($salesOrder ?? null)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ salesOrder }}', $value)
            ->addViolation();
    }
}
