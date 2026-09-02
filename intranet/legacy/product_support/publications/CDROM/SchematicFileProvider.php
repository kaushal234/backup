<?php
declare(strict_types=1);

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

require_once 'FileGenerator.php';

class SchematicFileProvider
{
    public $client;
    private $manualDirectory;
    private $fileGenerator;

    public function __construct(SchematicsTmpDirectory $manualDirectory)
    {
        global $kernel;
        $container = $kernel->getContainer();
        $this->client = $container->get(Client::class);
        $this->manualDirectory = $manualDirectory;
        $this->fileGenerator = new FileGenerator();
    }

    public function get(array $schematic, tldEquipment $equipment)
    {
        try {
            $fileResponse = $this->client->request(
                sprintf('ion/bill-of-materials/drawings/site=%d;project=;product=%s',
                    $schematic["brand"],
                    $schematic["serial"]
                ),
                null,
                null,
                Client::REQUEST_GET
            );

            return $this->fileGenerator->generateFromContent($fileResponse->getContent(), sprintf('%s/%s', $this->manualDirectory->path, $schematic["serial"]));
        } catch (ClientException $exception) {
            return false;
        }
    }
}