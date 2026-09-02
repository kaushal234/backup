<?php

declare(strict_types=1);

namespace App\Factory\Service;

use App\Dto\Service\TechnicianOnCallDuplicateLineInput;
use App\Entity\Service\TechnicianOnCall;

final readonly class TechnicianOnCallDuplicateFactory
{
    public function duplicate(TechnicianOnCall $original, TechnicianOnCallDuplicateLineInput $inputLine): TechnicianOnCall
    {
        $toc = $this->cloneFromOriginal($original);

        return $this->applyInputLine($toc, $inputLine);
    }

    public function cloneFromOriginal(TechnicianOnCall $original): TechnicianOnCall
    {
        return clone $original;
    }

    public function applyInputLine(TechnicianOnCall $toc, TechnicianOnCallDuplicateLineInput $inputLine): TechnicianOnCall
    {
        $toc->equipmentRecord = $inputLine->equipmentRecord;
        $toc->airport = $inputLine->airport;
        $toc->salesOrganisationService = $inputLine->salesOrganisationService;
        $toc->status = TechnicianOnCall::IN_PROGRESS;
        $toc->createdBy = null;
        $toc->nestedCustomerServiceRecord = $inputLine->nestedCustomerServiceRecord;
        $toc->hourMeter = $inputLine->hourMeter;
        $toc->factoryFlag = false;
        $toc->customer = $inputLine->equipmentRecord?->getEndUser();

        return $toc;
    }
}
