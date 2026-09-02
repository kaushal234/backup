<?php

declare(strict_types=1);

namespace App\Client;

use App\Client\Exception\SoapException;
use App\Client\Parser\SoapCallParser;
use App\ION\DataProvider\AbstractIONDataProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SoapClient implements SoapClientInterface
{
    private readonly \SoapClient $decorated;
    private array $calls = [];
    private readonly RequestStack $requestStack;
    private readonly Filesystem $fileSystem;
    private readonly SoapCallParser $callParser;
    /** @var SerializerInterface|Serializer */
    private readonly SerializerInterface $serializer;
    private readonly string $projectDir;

    private bool $soapCallEnabled = true;
    private bool $record = false;
    private array $lastRequest = [];

    private readonly LoggerInterface $ionRequestLogger;

    public function __construct(
        \SoapClient $decorated,
        RequestStack $requestStack,
        Filesystem $fileSystem,
        SoapCallParser $callParser,
        SerializerInterface $serializer,
        string $projectDir,
        LoggerInterface $ionRequestLogger,
    ) {
        $this->decorated = $decorated;
        $this->requestStack = $requestStack;
        $this->fileSystem = $fileSystem;
        $this->callParser = $callParser;
        $this->serializer = $serializer;
        $this->projectDir = $projectDir;
        $this->ionRequestLogger = $ionRequestLogger;
    }

    public function __call($name, $arguments)
    {
        $exception = null;

        try {
            $argument = current($arguments);
            if (!$this->soapCallEnabled || $this->record) {
                $argument = $this->callParser->parse($argument);
            }

            $this->calls[$name][] = $argument;
            $this->lastRequest = [$name => $argument];

            if ($this->soapCallEnabled) {
                $thrownException = null;
                try {
                    $this->decorated->__soapCall($name, $arguments);
                } catch (\Exception $exception) {
                    $thrownException = $exception;
                }

                $this->ionRequestLogger->debug('request: {request}, response: {response}', [
                    'request' => $this->__getLastRequest(),
                    'response' => $this->__getLastResponse(),
                ]);

                /** @var Request $request */
                $request = $this->requestStack->getCurrentRequest();
                if ($this->record) {
                    $doc = new \DOMDocument('1.0');
                    $doc->loadXML($this->decorated->__getLastResponse());
                    $doc->preserveWhiteSpace = false;
                    $doc->formatOutput = true;
                    $this->fileSystem->dumpFile($this->getFixtureFolder($request).$this->getFixtureFilename($request), $doc->saveXML());
                }
                if (null !== $thrownException) {
                    throw $thrownException;
                }
            }
        } catch (\Exception $e) {
            $exception = $e;
        }

        if ($exception instanceof \Throwable) {
            throw $exception;
        }

        if (!$this->soapCallEnabled && !$this->record && null !== $this->__getLastResponse() && $this->serializer instanceof DecoderInterface) {
            $xml = $this->serializer->decode($this->__getLastResponse(), XmlEncoder::FORMAT);
            if (null !== ($fault = $xml['S:Body']['S:Fault'] ?? null)) {
                $result = $fault['detail']['Result:Result'] ?? $fault['detail']['Result'];
                throw new \SoapFault($fault['faultcode'], $fault['faultstring'], null, isset($fault['detail']) ? ['Result' => $result] : '');
            }
        }
    }

    public function __getLastRequest(): ?string
    {
        return $this->decorated->__getLastRequest();
    }

    public function __getLastResponse(): ?string
    {
        if (!$this->soapCallEnabled && !$this->record) {
            /** @var Request $request */
            $request = $this->requestStack->getCurrentRequest();
            if (!$this->fileSystem->exists($fixture = ($this->getFixtureFolder($request).$this->getFixtureFilename($request)))) {
                $soapFault = new \SoapFault('fixture_file_not_found', \sprintf('Fixture file %s not found', $fixture));
                // TODO manage exception and soap fault
                throw SoapException::createFromSoapFault($request->attributes->get(AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE, 'Unknown'), $soapFault);
            }

            return file_get_contents($fixture);
        }

        return $this->decorated->__getLastResponse();
    }

    public function __getLastRequestHeaders(): ?string
    {
        return $this->decorated->__getLastRequestHeaders();
    }

    public function __getLastResponseHeaders(): ?string
    {
        return $this->decorated->__getLastResponseHeaders();
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function disableSoapCalls()
    {
        $this->soapCallEnabled = false;
        $this->record = false;
    }

    public function enableRecord()
    {
        $this->soapCallEnabled = true;
        $this->record = true;
    }

    private function getFixtureFolder(Request $request): string
    {
        return \sprintf('%s/%s/', $this->projectDir.'/tests/fixtures/soap', mb_trim($request->getPathInfo(), '/'));
    }

    private function getFixtureFilename(Request $request): string
    {
        return \sprintf('%s.xml', hash('sha256', serialize([
            'url' => $request->getPathInfo(),
            'parameters' => $request->query->all(),
            'soap_request' => $this->lastRequest,
        ])));
    }
}
