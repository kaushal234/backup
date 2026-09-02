<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Header;

use App\SageParts\Models\Header\From;

class FromFactory
{
    private CredentialFactory $credentialFactory;

    public function __construct(CredentialFactory $credentialFactory)
    {
        $this->credentialFactory = $credentialFactory;
    }

    public function create(): From
    {
        $from = new From();
        $from->setCredential($this->credentialFactory->create('TLD.com', 'support@TLD.com'));

        return $from;
    }
}
