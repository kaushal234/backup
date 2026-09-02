<?php

declare(strict_types=1);

namespace App\Client\WSDL;

use Symfony\Component\Filesystem\Filesystem;

class WSDLManager
{
    private readonly Filesystem $fileSystem;
    private readonly string $projectDir;

    public function __construct(Filesystem $fileSystem, string $projectDir)
    {
        $this->fileSystem = $fileSystem;
        $this->projectDir = $projectDir;
    }

    public function getWSDLFilePath(string $ionResource): string
    {
        return \sprintf($this->projectDir.'/config/WSDL/%s.wsdl', $ionResource);
    }

    public function switchToEnvironment(string $ionResource, string $environment)
    {
        $document = new \DOMDocument();
        $document->load($filePath = $this->getWSDLFilePath($ionResource));

        /** @var \DOMNamedNodeMap $addressAttributes */
        $addressAttributes = $document
            ->getElementsByTagName('definitions')
            ->item(0)
            ->getElementsByTagName('address')
            ->item(0)
            ->attributes
        ;
        /** @var \DOMAttr $location */
        $location = $addressAttributes->getNamedItem('location');
        if (null === $location->value = preg_replace('/TLD_\w*[^\/]/', \sprintf('TLD_%s', $environment), $location->value, 1)) {
            throw new \LogicException(\sprintf('Could not find any occurrence of TLD_* in %s', $filePath));
        }

        $this->fileSystem->dumpFile($filePath, $document->saveXML());
    }
}
