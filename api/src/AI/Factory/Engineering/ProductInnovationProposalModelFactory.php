<?php

declare(strict_types=1);

namespace App\AI\Factory\Engineering;

use App\AI\Dto\Engineering\ProductInnovationProposalModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Engineering\ProductInnovationProposal;

final readonly class ProductInnovationProposalModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return ProductInnovationProposal::class === $class;
    }

    /**
     * @param ProductInnovationProposal $entity
     */
    public function create(object $entity): ProductInnovationProposalModel
    {
        return new ProductInnovationProposalModel(
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            process: $entity->process,
            productType: $entity->productType,
            model: $entity->model,
            status: $entity->status,
            submittedAt: $entity->submittedAt,
            closedAt: $entity->closedAt,
            suspendedAt: $entity->suspendedAt,
            suspendedDays: $entity->suspendedDays,
            resolution: $entity->resolution,
            rejectionReason: $entity->rejectionReason,
            importanceFactor: $entity->importanceFactor,
            finalWeight: $entity->finalWeight,
            factory: null !== $entity->factory ? $this->locationModelFactory->create($entity->factory) : null,
            poster: null !== $entity->poster ? $this->peopleModelFactory->create($entity->poster) : null,
            initiator: null !== $entity->initiator ? $this->peopleModelFactory->create($entity->initiator) : null,
        );
    }
}
