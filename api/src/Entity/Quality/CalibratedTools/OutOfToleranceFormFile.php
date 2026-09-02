<?php

declare(strict_types=1);

namespace App\Entity\Quality\CalibratedTools;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'out_of_tolerance_form_files')]
class OutOfToleranceFormFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\CalibratedTools\OutOfToleranceForm', inversedBy: 'files')]
    private ?OutOfToleranceForm $outOfToleranceForm = null;

    public function getOutOfToleranceForm(): OutOfToleranceForm
    {
        return $this->outOfToleranceForm;
    }

    /**
     * @return $this
     */
    public function setOutOfToleranceForm(OutOfToleranceForm $outOfToleranceForm)
    {
        $this->outOfToleranceForm = $outOfToleranceForm;

        return $this;
    }
}
