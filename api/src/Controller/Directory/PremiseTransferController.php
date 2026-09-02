<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use App\Entity\Directory\Premise;
use App\Manager\Transfer\EntityTransferManager;
use App\Manager\Transfer\Model\TransferPremiseModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class PremiseTransferController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly EntityTransferManager $transferManager;

    public function __construct(SerializerInterface $serializer, EntityTransferManager $transferManager)
    {
        $this->serializer = $serializer;
        $this->transferManager = $transferManager;
    }

    public function __invoke(Premise $premise, Request $request)
    {
        /** @var TransferPremiseModel $transferParams */
        $transferParams = $this->serializer->deserialize($request->getContent(), TransferPremiseModel::class, JsonEncoder::FORMAT);

        $this->transferManager->transfer($premise, $transferParams->target);

        return $premise;
    }
}
