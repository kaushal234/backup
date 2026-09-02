<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Header;

use App\SageParts\Models\Header\To;

class ToFactory
{
    private CredentialFactory $credentialFactory;

    public function __construct(CredentialFactory $credentialFactory)
    {
        $this->credentialFactory = $credentialFactory;
    }

    public function create(): To
    {
        $to = new To();
        $to->setCredential($this->credentialFactory->create());

        return $to;
    }
}
