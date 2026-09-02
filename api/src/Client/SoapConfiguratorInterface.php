<?php

declare(strict_types=1);

namespace App\Client;

interface SoapConfiguratorInterface
{
    public function getWsdl(?string $resourceName = null): string;

    public function getAdditionalOptions(bool $authenticated): array;

    public function addHeaders(string $resourceName): ?\SoapHeader;

    public function getClientName(): string;
}
