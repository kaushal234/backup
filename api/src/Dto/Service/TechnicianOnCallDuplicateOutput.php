<?php

declare(strict_types=1);

namespace App\Dto\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

final class TechnicianOnCallDuplicateOutput
{
    /** @var Collection<TechnicianOnCallDuplicateLineOutput> */
    #[Groups(['toc:read'])]
    public Collection $outputLines;

    public function __construct()
    {
        $this->outputLines = new ArrayCollection();
    }

    public function addLine(TechnicianOnCallDuplicateLineOutput $outputLine): self
    {
        if (!$this->outputLines->contains($outputLine)) {
            $this->outputLines->add($outputLine);
        }

        return $this;
    }
}
