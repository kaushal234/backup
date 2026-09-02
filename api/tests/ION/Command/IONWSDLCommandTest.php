<?php

declare(strict_types=1);

namespace App\Tests\ION\Command;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use App\Client\WSDL\WSDLManager;
use App\ION\Command\IONWSDLCommand;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class IONWSDLCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'ion:wsdl';

    public function testExecuteWithDefaultEnvironment()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand($this->getCommand('DTC'));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }

    public function testExecuteWithCustomEnvironment()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand($this->getCommand('WTF'));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
            'environment' => 'WTF',
        ]);
    }

    private function getCommand(string $expectedEnvironment): IONWSDLCommand
    {
        $fakeResources = ['ION\\ZION\\LION' => 'a_tribute', 'Poire\\Belle\\Helene' => null];

        $resourceNameCollectionFactoryProphecy = $this->prophesize(ResourceNameCollectionFactoryInterface::class);
        $resourceNameCollectionFactoryProphecy->create()->shouldBeCalledOnce()->willReturn(new ResourceNameCollection(array_keys($fakeResources)));
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $WSDLManagerProphecy = $this->prophesize(WSDLManager::class);

        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('ION\\ZION\\LION')->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $sourceProviderProphecy->getResourceSourceProvider('Poire\\Belle\\Helene')->shouldBeCalledOnce()->willThrow(new UnprocessableEntityHttpException());

        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('a_tribute');

        $WSDLManagerProphecy->switchToEnvironment('a_tribute', $expectedEnvironment)->shouldBeCalledTimes(1);

        return new IONWSDLCommand(
            $resourceNameCollectionFactoryProphecy->reveal(),
            $sourceProviderProphecy->reveal(),
            $WSDLManagerProphecy->reveal(),
            'DTC'
        );
    }
}
