<?php

declare(strict_types=1);

namespace App\SageParts\Models\Header;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Header
{
    #[SerializedName('From')]
    private From $from;

    #[SerializedName('To')]
    private ?To $to = null;

    #[SerializedName('Sender')]
    private ?Sender $sender = null;

    public function getFrom(): From
    {
        return $this->from;
    }

    public function setFrom(From $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): ?To
    {
        return $this->to;
    }

    public function setTo(?To $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getSender(): ?Sender
    {
        return $this->sender;
    }

    public function setSender(?Sender $sender): self
    {
        $this->sender = $sender;

        return $this;
    }
}
