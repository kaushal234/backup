<?php

declare(strict_types=1);

namespace App\Client;

use App\Client\Parser\SoapCallParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class SoapClientFactory implements SoapClientFactoryInterface
{
    private bool $authenticationEnabled = true;

    /** @var array|SoapClientInterface[] */
    private array $clients = [];
    private bool $cacheEnabled = true;
    private readonly RequestStack $requestStack;
    private readonly Filesystem $filesystem;
    private readonly SoapCallParser $callParser;
    private readonly SerializerInterface $serializer;
    private readonly string $projectDir;

    /** @var iterable|SoapConfiguratorInterface[] */
    private iterable $clientConfigurators;

    private readonly LoggerInterface $ionRequestLogger;

    public function __construct(
        #[AutowireIterator('app.soap_client_configurator')] iterable $clientConfigurators,
        RequestStack $requestStack,
        Filesystem $filesystem,
        SoapCallParser $callParser,
        SerializerInterface $serializer,
        string $projectDir,
        LoggerInterface $ionRequestLogger,
    ) {
        $this->requestStack = $requestStack;
        $this->filesystem = $filesystem;
        $this->callParser = $callParser;
        $this->serializer = $serializer;
        $this->projectDir = $projectDir;
        $this->ionRequestLogger = $ionRequestLogger;

        foreach ($clientConfigurators as $clientConfigurator) {
            if (!$clientConfigurator instanceof SoapConfiguratorInterface) {
                throw new \Exception(\sprintf('Tagged services %s "app.soap_client_factory" must implement SoapConfiguratorInterface', $clientConfigurator::class));
            }

            $this->clientConfigurators[$clientConfigurator->getClientName()] = $clientConfigurator;
        }
    }

    public function createClient(string $clientName, string $resourceName): SoapClientInterface
    {
        if (null !== ($client = $this->getClient($resourceName))) {
            return $client;
        }

        $options = [
            'trace' => true,
            'exceptions' => true,
            'keep_alive' => true,
            'connection_timeout' => 10,
            'features' => \SOAP_USE_XSI_ARRAY_TYPE,
        ];

        if (!$this->cacheEnabled) {
            $options['cache_wsdl'] = \WSDL_CACHE_NONE;
        }

        $options = [...$options, ...$this->clientConfigurators[$clientName]->getAdditionalOptions($this->authenticationEnabled)];

        $client = new \SoapClient($this->clientConfigurators[$clientName]->getWsdl($resourceName), $options);

        if (null !== $soapHeader = $this->clientConfigurators[$clientName]->addHeaders($resourceName)) {
            $client->__setSoapHeaders($soapHeader);
        }

        $IONClient = new SoapClient($client, $this->requestStack, $this->filesystem, $this->callParser, $this->serializer, $this->projectDir, $this->ionRequestLogger);
        $this->addClient($resourceName, $IONClient);

        return $IONClient;
    }

    public function getClient(string $resourceName): ?SoapClientInterface
    {
        return $this->clients[$resourceName] ?? null;
    }

    public function getClients(): array
    {
        return $this->clients;
    }

    public function disableCache(): void
    {
        $this->cacheEnabled = false;
    }

    public function disableAuthentication(): void
    {
        $this->authenticationEnabled = false;
    }

    protected function addClient(string $resourceName, SoapClientInterface $client)
    {
        $this->clients[$resourceName] = $client;
    }
}
