<?php

declare(strict_types=1);

namespace App\ION\DataProcessor\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Activity\Comment;
use App\Entity\Parts\SparePartsRequest;
use App\Notifier\Parts\SparePartsRequestNotifier;
use App\Repository\Parts\SparePartsRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T
 */
class ShippedSalesOrderDataProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SparePartsRequestRepository $sparePartsRequestRepository,
        private readonly SparePartsRequestNotifier $sparePartsRequestNotifier,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        foreach ($this->sparePartsRequestRepository->findAndUpdateUnshippedBySalesOrderNumber($data->salesOrderNumber) as $sparePartsRequest) {
            $data->updatedSparePartsRequests[] = $sparePartsRequest->getId();
            $comment =
                (new Comment())
                    ->setMessage(\sprintf(
                        'Sales Order #%s was closed in LN, the SPR status has been switched to %s',
                        $data->salesOrderNumber, SparePartsRequest::STATUS_SHIPPED
                    ))
                    ->setResource($this->iriConverter->getIriFromResource($sparePartsRequest))
            ;
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            $this->sparePartsRequestNotifier->notifyShipping($sparePartsRequest);
        }

        return $data;
    }
}
