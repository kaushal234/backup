<?php

declare(strict_types=1);

namespace App\Controller\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastStatusUpdateModel;
use App\Request\Activity\CommentRequestManager;
use App\Request\Sales\SalesForecastRequestManager;
use App\Workflow\WorkflowStatusUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SalesForecastUpdateStatusController extends AbstractController
{
    private readonly WorkflowStatusUpdater $workflowStatusUpdater;
    private readonly DenormalizerInterface $serializer;
    private readonly SalesForecastRequestManager $salesForecastRequestManager;
    private readonly CommentRequestManager $commentRequestManager;

    public function __construct(WorkflowStatusUpdater $workflowStatusUpdater, DenormalizerInterface $serializer, SalesForecastRequestManager $salesForecastRequestManager, CommentRequestManager $commentRequestManager)
    {
        $this->workflowStatusUpdater = $workflowStatusUpdater;
        $this->serializer = $serializer;
        $this->salesForecastRequestManager = $salesForecastRequestManager;
        $this->commentRequestManager = $commentRequestManager;
    }

    public function __invoke(SalesForecast $data, Request $request)
    {
        $content = json_decode((string) ($request->getContent() ?: '{}'), true, 512, \JSON_THROW_ON_ERROR);

        /** @var SalesForecastStatusUpdateModel $updateData */
        $updateData = $this->serializer->denormalize($content, SalesForecastStatusUpdateModel::class, JsonEncoder::FORMAT);
        $status = $updateData->getStatus();

        if (null === $status) {
            throw new BadRequestHttpException('Status is mandatory');
        }

        $data->setComment($updateData->getComment());
        $data->setSynchronized($updateData->isSynchronized());

        try {
            if (SalesForecast::CANCELLED !== $status) {
                $data->setComment(\sprintf('Status changed to %s', $status));
            }

            $this->workflowStatusUpdater->applyStatus($data, $status);

            if (SalesForecast::CANCELLED === $status && $updateData->isCancellationPropagated() && null !== $data->getMasterSalesForecast()) {
                foreach ($data->getMasterSalesForecast()->getSalesForecasts() as $salesForecast) {
                    if ($salesForecast->getId() === $data->getId()) {
                        continue;
                    }
                    $this->salesForecastRequestManager->updateStatus($salesForecast, SalesForecast::CANCELLED, ['comment' => $data->getComment()]);
                    $this->commentRequestManager->insertComment($salesForecast, $updateData->getComment());
                }
            }
        } catch (\Exception $exception) {
            $authorizationChecker = $this->container->get('security.authorization_checker');
            if ($authorizationChecker->isGranted('MOO_SFR') || $authorizationChecker->isGranted('FEATURE_SALES_FORECAST_FORCE_STATUS')) {
                $data->setStatus($status);

                return $data;
            }

            throw new BadRequestHttpException(\sprintf('Status %s is not allowed', $status), $exception);
        }

        return $data;
    }
}
