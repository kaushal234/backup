<?php

declare(strict_types=1);

namespace Alvest\Translator\Provider;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Component\Translation\Bridge\Crowdin\CrowdinProviderFactory as SymfonyCrowdinProviderFactory;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Exception\UnsupportedSchemeException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Provider\AbstractProviderFactory;
use Symfony\Component\Translation\Provider\Dsn;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function sprintf;

final class CrowdinProviderFactory extends AbstractProviderFactory
{
    private const HOST = 'api.crowdin.com';

    public function __construct(
        private readonly SymfonyCrowdinProviderFactory $decoratedFactory,
        private readonly HttpClientInterface $client,
        private readonly LoaderInterface $loader,
        private readonly string $defaultLocale,
        private readonly XliffFileDumper $xliffFileDumper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function create(Dsn $dsn): CrowdinProvider
    {
        if ('crowdin' !== $dsn->getScheme()) {
            throw new UnsupportedSchemeException($dsn, 'crowdin', $this->getSupportedSchemes());
        }

        $endpoint = preg_replace('/(^|\.)default$/', '\1'.self::HOST, $dsn->getHost());
        $endpoint .= $dsn->getPort() ? ':'.$dsn->getPort() : '';

        $client = ScopingHttpClient::forBaseUri($this->client, sprintf('https://%s/api/v2/projects/%d/', $endpoint, $this->getUser($dsn)), [
            'auth_bearer' => $this->getPassword($dsn),
        ], preg_quote('https://'.$endpoint.'/api/v2/'));

        $decoratedProvider = $this->decoratedFactory->create($dsn);

        return new CrowdinProvider($decoratedProvider, $client, $this->loader, $this->defaultLocale, $this->xliffFileDumper, $this->logger);
    }

    protected function getSupportedSchemes(): array
    {
        return ['crowdin'];
    }
}
