<?php

declare(strict_types=1);

namespace App\Controller\Purchasing;

use App\Entity\Purchasing\SupplierRanking\ExpertiseLevel;
use App\Manager\Transfer\EntityTransferManager;
use App\Manager\Transfer\Model\TransferExpertiseLevelModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ExpertiseLevelTransferController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly EntityTransferManager $transferManager;

    public function __construct(SerializerInterface $serializer, EntityTransferManager $transferManager)
    {
        $this->serializer = $serializer;
        $this->transferManager = $transferManager;
    }

    public function __invoke(ExpertiseLevel $expertiseLevel, Request $request)
    {
        /** @var TransferExpertiseLevelModel $transferParams */
        $transferParams = $this->serializer->deserialize($request->getContent(), TransferExpertiseLevelModel::class, JsonEncoder::FORMAT);
        $this->transferManager->transfer($expertiseLevel, $transferParams->target);

        return $expertiseLevel;
    }
}
