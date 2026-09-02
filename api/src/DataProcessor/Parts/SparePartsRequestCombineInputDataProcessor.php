<?php

declare(strict_types=1);

namespace App\DataProcessor\Parts;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\Parts\SparePartsRequestCombineInput;
use App\Entity\Activity\Activity;
use App\Entity\Parts\SparePartsRequest;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T
 */
class SparePartsRequestCombineInputDataProcessor implements ProcessorInterface
{
    private readonly ValidatorInterface $validator;
    private readonly CommentRequestManager $commentRequestManager;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ValidatorInterface $validator, CommentRequestManager $commentRequestManager, EntityManagerInterface $entityManager)
    {
        $this->validator = $validator;
        $this->commentRequestManager = $commentRequestManager;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param SparePartsRequestCombineInput $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var SparePartsRequest $originalSparePartsRequest */
        $originalSparePartsRequest = $context['previous_data'];
        $data->originalSparePartsRequest = $originalSparePartsRequest;

        $this->validator->validate($data);

        $activityRepository = $this->entityManager->getRepository(Activity::class);

        foreach ($data->getSparePartsRequests() as $sparePartsRequest) {
            $originalSparePartsRequest->importPartsFromSparePartsRequest($sparePartsRequest);
            $this->commentRequestManager->insertComment(
                $originalSparePartsRequest,
                \sprintf('SPR#%1$s parts were imported and SPR#%1$s has been closed', $sparePartsRequest->getId())
            );
            $activityRepository->replaceActivity($sparePartsRequest, $originalSparePartsRequest);
            $sparePartsRequest->notes = \sprintf(
                "SPR#%s parts were combined with SPR#%s \n %s",
                $sparePartsRequest->getId(),
                $originalSparePartsRequest->getId(),
                $sparePartsRequest->notes
            );
            $sparePartsRequest->setStatus(SparePartsRequest::STATUS_MERGED);
            $this->entityManager->persist($sparePartsRequest);
        }

        $this->entityManager->persist($originalSparePartsRequest);
        $this->entityManager->flush();

        return $originalSparePartsRequest;
    }
}
