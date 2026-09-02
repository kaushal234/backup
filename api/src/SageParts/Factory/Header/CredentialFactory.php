<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Header;

use App\SageParts\Models\Header\Credential;

class CredentialFactory
{
    public function create(string $domain = '', string $identity = '', ?string $sharedSecret = null, ?string $userAgent = null): Credential
    {
        $credential = new Credential();
        $credential
            ->setDomain($domain)
            ->setIdentity($identity)
            ->setSharedSecret($sharedSecret)
            ->setUserAgent($userAgent)
        ;

        return $credential;
    }
}
