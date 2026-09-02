<?php

declare(strict_types=1);

namespace App\Dto\Service;

use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class TechnicianOnCallEmailInput
{
    #[Assert\NotNull]
    #[Groups(['toc:write'])]
    public string $subject;

    #[Groups(['toc:write'])]
    public ?string $message = null;

    #[Assert\NotNull]
    #[Groups(['toc:write'])]
    public People $to;

    /**
     * @var Collection<People>
     */
    #[Assert\All([new Assert\Type(type: People::class)])]
    private readonly Collection $ccs;

    /**
     * @var Collection<People>
     */
    #[Assert\All([new Assert\Type(type: People::class)])]
    private readonly Collection $bccs;

    public function __construct()
    {
        $this->bccs = new ArrayCollection();
        $this->ccs = new ArrayCollection();
    }

    /**
     * @return Collection<People>
     */
    public function getCcs(): Collection
    {
        return $this->ccs;
    }

    public function addCc(People $cc): self
    {
        $this->ccs->add($cc);

        return $this;
    }

    public function removeCc(People $cc): self
    {
        $this->ccs->removeElement($cc);

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getBccs(): Collection
    {
        return $this->bccs;
    }

    public function addBcc(People $bcc): self
    {
        $this->bccs->add($bcc);

        return $this;
    }

    public function removeBcc(People $bcc): self
    {
        $this->bccs->removeElement($bcc);

        return $this;
    }
}
