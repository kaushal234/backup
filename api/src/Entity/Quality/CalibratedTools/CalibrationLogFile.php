<?php

declare(strict_types=1);

namespace App\Entity\Quality\CalibratedTools;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'calibration_log_files')]
#[App\Loggable(owner: 'calibrationLog', ownerRelation: 'files')]
class CalibrationLogFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\CalibratedTools\CalibrationLog', inversedBy: 'files')]
    private ?CalibrationLog $calibrationLog = null;

    public function getCalibrationLog(): CalibrationLog
    {
        return $this->calibrationLog;
    }

    public function setCalibrationLog(CalibrationLog $calibrationlog): self
    {
        $this->calibrationLog = $calibrationlog;

        return $this;
    }
}
