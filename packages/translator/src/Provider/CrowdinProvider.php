<?php

declare(strict_types=1);

namespace Alvest\Translator\Provider;

use Psr\Log\LoggerInterface;
use ReflectionObject;
use Symfony\Component\Translation\Bridge\Crowdin\CrowdinProvider as SymfonyCrowdinProvider;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Exception\ProviderException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Provider\ProviderInterface;
use Symfony\Component\Translation\TranslatorBag;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use ZipArchive;

use function count;
use function in_array;
use function sprintf;

final class CrowdinProvider implements ProviderInterface
{
    public function __construct(
        private readonly SymfonyCrowdinProvider $decoratedProvider,
        private readonly HttpClientInterface $client,
        private readonly LoaderInterface $loader,
        private readonly string $defaultLocale,
        private readonly XliffFileDumper $xliffFileDumper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->decoratedProvider;
    }

    public function write(TranslatorBagInterface $translatorBag): void
    {
        $reflector = new ReflectionObject($this->decoratedProvider);
        $getFileIdByDomainMethod = $reflector->getMethod('getFileIdByDomain');
        $getFileIdByDomainMethod->setAccessible(true);
        $addFileMethod = $reflector->getMethod('addFile');
        $addFileMethod->setAccessible(true);
        $updateFileMethod = $reflector->getMethod('updateFile');
        $updateFileMethod->setAccessible(true);
        $uploadTranslationsMethod = $reflector->getMethod('uploadTranslations');
        $uploadTranslationsMethod->setAccessible(true);

        $fileList = $this->getFileList();
        $responses = [];

        /** @var MessageCatalogue $catalogue */
        foreach ($translatorBag->getCatalogues() as $catalogue) {
            foreach ($catalogue->getDomains() as $domain) {
                if (0 === count($catalogue->all($domain))) {
                    continue;
                }

                $content = $this->xliffFileDumper->formatCatalogue($catalogue, $domain, ['default_locale' => $this->defaultLocale]);

                $fileId = $getFileIdByDomainMethod->invoke($this->decoratedProvider, $fileList, $domain);

                if ($catalogue->getLocale() === $this->defaultLocale) {
                    if (!$fileId) {
                        $file = $addFileMethod->invoke($this->decoratedProvider, $domain, $content);
                    } else {
                        $file = $updateFileMethod->invoke($this->decoratedProvider, $fileId, $domain, $content);
                    }

                    if (!$file) {
                        continue;
                    }

                    $fileList[$file['name']] = $file['id'];
                } else {
                    if (!$fileId) {
                        continue;
                    }

                    $responses[] = $uploadTranslationsMethod->invoke($this->decoratedProvider, $fileId, $domain, $content, $catalogue->getLocale());
                }
            }
        }

        foreach ($responses as $response) {
            if (200 !== $statusCode = $response->getStatusCode()) {
                $this->logger->error(sprintf('Unable to upload translations to Crowdin: "%s".', $response->getContent(false)));

                if (500 <= $statusCode) {
                    throw new ProviderException('Unable to upload translations to Crowdin.', $response);
                }
            }
        }
    }

    /**
     * @param array<string> $domains
     * @param array<string> $locales
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function read(array $domains, array $locales): TranslatorBag
    {
        $translatorBag = new TranslatorBag();
        $responses = [];

        $response = $this->buildProject();
        if (201 !== $response->getStatusCode()) {
            throw new ProviderException('Error on building project.', $response);
        }

        $buildId = $response->toArray()['data']['id'];

        $attempt = 1;
        while ($response = $this->downloadProjectBuild($buildId)) {
            sleep(1);
            if (200 === $response->getStatusCode()) {
                break;
            }
            if (202 === $response->getStatusCode()) {
                if ($attempt <= 5) {
                    ++$attempt;
                    continue;
                }
            }
            throw new ProviderException('Cannot retrieve project build.', $response);
        }

        $response = $this->client->request('GET', $response->toArray()['data']['url']);

        $tmpfname = tempnam('/tmp', 'crowdin_');
        $handle = fopen($tmpfname, 'w');
        fwrite($handle, $response->getContent());
        fclose($handle);

        $zipArchive = new ZipArchive();
        $zipArchive->open($tmpfname);

        for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
            $pathInfo = pathinfo($zipArchive->getNameIndex($i));
            if ('.' === $pathInfo['dirname']) {
                continue;
            }

            if (in_array($pathInfo['filename'], $domains, true) && in_array($pathInfo['dirname'], $locales, true)) {
                $translatorBag->addCatalogue($this->loader->load($zipArchive->getFromIndex($i), $pathInfo['dirname'], $pathInfo['filename']));
            }
        }

        return $translatorBag;
    }

    public function delete(TranslatorBagInterface $translatorBag): void
    {
        $this->decoratedProvider->delete($translatorBag);
    }

    /**
     * @return array<string, string>
     */
    private function getFileList(): array
    {
        $result = [];

        $response = $this->client->request('GET', 'files', [
            'query' => [
                'limit' => 200,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new ProviderException('Unable to list Crowdin files.', $response);
        }

        $fileList = $response->toArray()['data'];

        foreach ($fileList as $file) {
            $result[$file['data']['name']] = $file['data']['id'];
        }

        return $result;
    }

    private function buildProject(): ResponseInterface
    {
        $response = $this->client->request('POST', 'translations/builds', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response;
    }

    private function downloadProjectBuild(int $buildId): ResponseInterface
    {
        $response = $this->client->request('GET', sprintf('translations/builds/%d/download', $buildId), [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response;
    }
}
