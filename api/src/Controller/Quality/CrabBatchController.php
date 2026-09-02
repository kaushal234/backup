<?php

declare(strict_types=1);

namespace App\Controller\Quality;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Dto\Quality\CrabBatch;
use App\Factory\Quality\CrabFactory;
use App\Manager\Quality\CrabManager;
use App\Message\Quality\Crab\CrabWrite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CrabBatchController extends AbstractController
{
    public function __construct(
        private readonly CrabFactory $crabFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly IriConverterInterface $iriConverter,
        private readonly CrabManager $crabManager,
        private readonly NormalizerInterface $normalizer
    ) {
    }

    public function __invoke(CrabBatch $data): JsonResponse
    {
        $user = $this->iriConverter->getIriFromResource($this->getUser());
        $newCrabs = [];

        foreach ($data->getEquipmentRecords() as $equipmentRecord) {
            try {
                $crab = $this->crabFactory->createCrab($data->crab, $equipmentRecord);
            } catch (ValidationException $exception) {
                $newCrabs['errorForEquipment'][$equipmentRecord->getSerialNumber()] = $exception->getMessage();
                continue;
            }

            $this->entityManager->persist($crab);
            $this->entityManager->flush();
            $this->crabManager->updateIONCrabData($equipmentRecord);
            $this->messageBus->dispatch(new CrabWrite($user, $this->iriConverter->getIriFromResource($crab), Request::METHOD_POST));
            $newCrabs['duplicateCrabId'][] = $crab->getId();
        }

        return new JsonResponse($newCrabs, JsonResponse::HTTP_CREATED);
    }
}
