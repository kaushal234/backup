<?php

declare(strict_types=1);

namespace App\ION\Command;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use App\Client\WSDL\WSDLManager;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[AsCommand(name: 'ion:wsdl')]
class IONWSDLCommand extends Command
{
    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly SourceProvider $sourceProvider,
        private readonly WSDLManager $WSDLManager,
        string $ionEnvironment
    ) {
        parent::__construct();
        $this
            ->setDescription('Update ION WSDL')
            ->addArgument('environment', InputArgument::OPTIONAL, 'Environment', $ionEnvironment)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $environment = $input->getArgument('environment');
        $logger->info('Updating WSDL files to switch to the {environment} environment', ['environment' => $environment]);

        $resourceNameCollection = $this->resourceNameCollectionFactory->create();
        $wsdlFilesParsed = [];
        foreach ($resourceNameCollection as $resourceName) {
            try {
                $ionResource = $this->sourceProvider->getResourceSourceProvider($resourceName)->getResource();
            } catch (UnprocessableEntityHttpException $exception) {
                continue;
            }

            if (null !== ($wsdlFilesParsed[$ionResource] ?? null)) {
                continue;
            }

            $logger->info('Updating WSDL file {ionResource} for resource {resource}', ['ionResource' => $ionResource, 'resource' => $resourceName]);

            try {
                $this->WSDLManager->switchToEnvironment($ionResource, $environment);
            } catch (\LogicException $exception) {
                $logger->critical($exception->getMessage());

                return Command::FAILURE;
            }

            $wsdlFilesParsed[$ionResource] = true;
        }

        return self::SUCCESS;
    }
}
