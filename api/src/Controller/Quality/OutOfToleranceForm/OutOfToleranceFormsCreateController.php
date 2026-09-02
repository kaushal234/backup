<?php

declare(strict_types=1);

namespace App\Controller\Quality\OutOfToleranceForm;

use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\OutOfToleranceForm;
use App\Entity\Quality\CalibratedTools\Tool;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OutOfToleranceFormsCreateController extends AbstractController
{
    public function __invoke(#[MapEntity(id: 'id')] Tool $tool, $data): OutOfToleranceForm
    {
        if ($tool->getCalibrationLogs()->isEmpty()) {
            throw new UnprocessableEntityHttpException('You are trying to create a out of tolerance form on a tool which have never been calibrated');
        }

        /** @var CalibrationLog $lastLog */
        $lastLog = $tool->getCalibrationLogs()->last();
        $data->setCalibrationLog($lastLog);
        $lastLog->setOutOfToleranceForm($data);

        return $data;
    }
}
