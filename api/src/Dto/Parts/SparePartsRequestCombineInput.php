<?php

declare(strict_types=1);

namespace App\Dto\Parts;

use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\TOCSparePartsRequest;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SparePartsRequestCombineInput
{
    public ?SparePartsRequest $originalSparePartsRequest = null;

    /**
     * @var SparePartsRequest[]
     */
    #[Assert\Count(min: 1)]
    private array $sparePartsRequests = [];

    public function addSparePartsRequest(SparePartsRequest $sparePartsRequest): self
    {
        $this->sparePartsRequests[] = $sparePartsRequest;

        return $this;
    }

    public function removeSparePartsRequest(SparePartsRequest $sparePartsRequest): self
    {
        // do nothing
        return $this;
    }

    public function getSparePartsRequests(): array
    {
        return $this->sparePartsRequests;
    }

    #[Assert\Callback]
    public function validateSparePartsRequests(ExecutionContextInterface $context)
    {
        if (null === $this->originalSparePartsRequest) {
            throw new \UnexpectedValueException('The original SPR must be populated in this object for validation');
        }

        foreach ($this->sparePartsRequests as $sparePartsRequest) {
            if (!\in_array($sparePartsRequest->getStatus(), [SparePartsRequest::STATUS_PENDING, SparePartsRequest::STATUS_OPEN], true)) {
                $context
                    ->buildViolation('All combined SPR must be either PENDING or OPEN')
                    ->atPath('sparePartsRequests')
                    ->addViolation();
            }

            if ($this->originalSparePartsRequest instanceof TOCSparePartsRequest) {
                if (!$sparePartsRequest instanceof TOCSparePartsRequest) {
                    $context
                        ->buildViolation('All combined SPR must originate from a TOC')
                        ->atPath('sparePartsRequests')
                        ->addViolation();
                    continue;
                }
                if ($sparePartsRequest->technicianOnCall->getId() !== $this->originalSparePartsRequest->technicianOnCall->getId()) {
                    $context
                        ->buildViolation('All combined SPR must originate from the same TOC')
                        ->atPath('sparePartsRequests')
                        ->addViolation();
                }
            }
            if ($this->originalSparePartsRequest instanceof SBSparePartsRequest) {
                if (!$sparePartsRequest instanceof SBSparePartsRequest) {
                    $context
                        ->buildViolation('All combined SPR must originate from a SB')
                        ->atPath('sparePartsRequests')
                        ->addViolation();
                    continue;
                }
                if ($sparePartsRequest->sbId !== $this->originalSparePartsRequest->sbId) {
                    $context
                        ->buildViolation('All combined SPR must originate from the same SB')
                        ->atPath('sparePartsRequests')
                        ->addViolation();
                }
            }
        }
    }
}
