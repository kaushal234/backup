<?php

declare(strict_types=1);

namespace App\Dto\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class TechnicianOnCallDuplicateInput
{
    /**
     * @var Collection<TechnicianOnCallDuplicateLineInput>
     */
    #[Groups(['toc:write', 'equipment_record:service'])]
    #[Assert\Count(min: 1)]
    public Collection $inputLines;

    public function __construct()
    {
        $this->inputLines = new ArrayCollection();
    }

    public function addInputLine(TechnicianOnCallDuplicateLineInput $inputLine): self
    {
        if (!$this->inputLines->contains($inputLine)) {
            $this->inputLines[] = $inputLine;
        }

        return $this;
    }

    public function removeInputLine(TechnicianOnCallDuplicateLineInput $inputLine): self
    {
        if ($this->inputLines->contains($inputLine)) {
            $this->inputLines->removeElement($inputLine);
        }

        return $this;
    }
}
