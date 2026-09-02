<?php

declare(strict_types=1);

namespace App\SageParts\Client;

use App\Client\SoapConfiguratorInterface;
use App\Client\WSDL\WSDLManager;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.soap_client_configurator')]
class SageSoapConfigurator implements SoapConfiguratorInterface
{
    public const CLIENT_NAME = 'sage';

    private WSDLManager $WSDLManager;

    public function __construct(WSDLManager $WSDLManager)
    {
        $this->WSDLManager = $WSDLManager;
    }

    public function getWsdl(?string $resourceName = null): string
    {
        // Download from https://edi2.sageparts.com/webservices/priceandavailability.asmx?WSDL
        return $this->WSDLManager->getWSDLFilePath('SagePriceAndAvailability');
    }

    public function getAdditionalOptions(bool $authenticated): array
    {
        return [];
    }

    public function addHeaders(string $resourceName): ?\SoapHeader
    {
        return null;
    }

    public function getClientName(): string
    {
        return self::CLIENT_NAME;
    }
}
