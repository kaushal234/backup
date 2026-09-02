<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\Twig;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Twig\Extension\ModuleExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ModuleExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testGetModule()
    {
        $data = new ApiData([]);

        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy
            ->findOneBy('modules', ['exact' => ['name' => 'MST']], ['cache' => true])
            ->shouldBeCalledTimes(1)
            ->willReturn($data)
        ;

        $extension = new ModuleExtension($clientProphecy->reveal());

        self::assertSame($data, $extension->getModule('MST'));
    }

    public function testGetModuleWithClientException()
    {
        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy
            ->findOneBy('modules', ['exact' => ['name' => 'MST']], ['cache' => true])
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception())
        ;

        $extension = new ModuleExtension($clientProphecy->reveal());

        self::assertNull($extension->getModule('MST'));
    }
}
