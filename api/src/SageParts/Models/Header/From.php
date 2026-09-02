<?php

declare(strict_types=1);

namespace App\SageParts\Models\Header;

use Symfony\Component\Serializer\Annotation\SerializedName;

class From
{
    #[SerializedName('Credential')]
    private Credential $credential;

    public function setCredential(Credential $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    public function getCredential(): Credential
    {
        return $this->credential;
    }
}
