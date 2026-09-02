<?php

declare(strict_types=1);

namespace App\ION\Client;

use App\Client\SoapConfiguratorInterface;
use App\Client\WSDL\WSDLManager;
use App\ION\Token\IONTokenProvider;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.soap_client_configurator')]
class IONSoapConfigurator implements SoapConfiguratorInterface
{
    public const CLIENT_NAME = 'ion';

    /** @var string */
    private const NAMESPACE = 'http://www.infor.com/businessinterface/';

    private IONTokenProvider $tokenProvider;
    private WSDLManager $WSDLManager;
    private int $ionCompany;

    public function __construct(IONTokenProvider $tokenProvider, WSDLManager $WSDLManager, int $ionCompany)
    {
        $this->tokenProvider = $tokenProvider;
        $this->WSDLManager = $WSDLManager;
        $this->ionCompany = $ionCompany;
    }

    public function getWsdl(?string $resourceName = null): string
    {
        return $this->WSDLManager->getWSDLFilePath($resourceName);
    }

    public function getAdditionalOptions(bool $authenticated): array
    {
        if (!$authenticated) {
            return [];
        }

        return ['stream_context' => stream_context_create(['http' => ['header' => "Authorization: Bearer {$this->tokenProvider->getToken()}"]])];
    }

    public function addHeaders(string $resourceName): ?\SoapHeader
    {
        return new \SoapHeader(self::NAMESPACE.$resourceName, 'Activation', ['company' => $this->ionCompany]);
    }

    public function getClientName(): string
    {
        return self::CLIENT_NAME;
    }
}
