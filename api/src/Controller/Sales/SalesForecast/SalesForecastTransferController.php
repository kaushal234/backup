<?php

declare(strict_types=1);

namespace App\Controller\Sales\SalesForecast;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Manager\Transfer\EntityTransferManager;
use App\Manager\Transfer\Model\TransferSalesForecastModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SalesForecastTransferController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly EntityTransferManager $transferManager;
    private readonly ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, EntityTransferManager $transferManager, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->transferManager = $transferManager;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {
        if (!$content = $request->getContent()) {
            $content = '{}';
        }
        /** @var TransferSalesForecastModel $transferParams */
        $transferParams = $this->serializer->deserialize($content, TransferSalesForecastModel::class, JsonEncoder::FORMAT);

        $violations = $this->validator->validate($transferParams);

        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->transferManager->transfer(null, $transferParams->getAsmTarget(), [
            'sso' => $transferParams->getSso(),
            'country' => $transferParams->getCountry(),
            'asmSource' => $transferParams->getAsmSource(),
            'buyer' => $transferParams->getBuyer(),
            'endUser' => $transferParams->getEndUser(),
        ]);

        return new Response(null, Response::HTTP_OK);
    }
}
