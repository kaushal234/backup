<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Header;

use App\SageParts\Models\Header\Sender;

class SenderFactory
{
    private CredentialFactory $credentialFactory;
    private string $sageSecret;

    public function __construct(CredentialFactory $credentialFactory, string $sageSecret)
    {
        $this->credentialFactory = $credentialFactory;
        $this->sageSecret = $sageSecret;
    }

    public function create(): Sender
    {
        $sender = new Sender();
        $sender->setCredential($this->credentialFactory->create('TLD.com', '', $this->sageSecret, 'Administrator'));

        return $sender;
    }
}
