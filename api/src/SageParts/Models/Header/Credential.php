<?php

declare(strict_types=1);

namespace App\SageParts\Models\Header;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Credential
{
    #[SerializedName('@domain')]
    private string $domain = '';

    #[SerializedName('Identity')]
    private string $identity = '';

    #[SerializedName('SharedSecret')]
    private ?string $sharedSecret = null;

    #[SerializedName('UserAgent')]
    private ?string $userAgent = null;

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getSharedSecret(): string
    {
        return $this->sharedSecret;
    }

    public function setSharedSecret(?string $sharedSecret): self
    {
        $this->sharedSecret = $sharedSecret;

        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }
}
