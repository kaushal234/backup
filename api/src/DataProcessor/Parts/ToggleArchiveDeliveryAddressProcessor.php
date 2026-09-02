<?php

declare(strict_types=1);

namespace App\DataProcessor\Parts;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Parts\SparePartsRequestDeliveryAddress;
use Doctrine\ORM\EntityManagerInterface;

class ToggleArchiveDeliveryAddressProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof SparePartsRequestDeliveryAddress) {
            throw new \RuntimeException('Unexpected data type');
        }

        $data->archived = !$data->archived;

        $this->entityManager->flush();

        return $data;
    }
}
