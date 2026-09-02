<?php

declare(strict_types=1);

namespace App\Controller\Purchasing;

use App\Entity\Purchasing\SupplierRanking\FileCategory;
use App\Manager\Transfer\EntityTransferManager;
use App\Manager\Transfer\Model\TransferFileCategoryModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class FileCategoryTransferController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly EntityTransferManager $transferManager;

    public function __construct(SerializerInterface $serializer, EntityTransferManager $transferManager)
    {
        $this->serializer = $serializer;
        $this->transferManager = $transferManager;
    }

    public function __invoke(FileCategory $fileCategory, Request $request)
    {
        /** @var TransferFileCategoryModel $transferParams */
        $transferParams = $this->serializer->deserialize($request->getContent(), TransferFileCategoryModel::class, JsonEncoder::FORMAT);
        $this->transferManager->transfer($fileCategory, $transferParams->target);

        return $fileCategory;
    }
}
