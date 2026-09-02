<?php

declare(strict_types=1);

namespace App\Controller\Quality\OutOfToleranceForm;

use App\Entity\Quality\CalibratedTools\OutOfToleranceForm;
use App\Workflow\WorkflowStatusUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Exception\LogicException;

class OutOfToleranceFormsUpdateController extends AbstractController
{
    private readonly WorkflowStatusUpdater $statusUpdater;

    public function __construct(WorkflowStatusUpdater $statusUpdater)
    {
        $this->statusUpdater = $statusUpdater;
    }

    public function __invoke(OutOfToleranceForm $outOfToleranceForm, $data): OutOfToleranceForm
    {
        try {
            $this->statusUpdater->applyStatus($outOfToleranceForm, $data->getStatus());
        } catch (LogicException $logicException) {
            throw new BadRequestHttpException(\sprintf('Status %s is not allowed', $data->getStatus()), $logicException);
        }

        return $data;
    }
}
