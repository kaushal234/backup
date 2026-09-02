<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\EventListener\Quality\FirstArticleQualification\FirstArticleQualificationPlanValidationListener;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FirstArticleQualificationPlanValidationListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testOnPlanItemCreationIgnoresNonFaq(): void
    {
        $serviceLocator = $this->prophesize(ContainerInterface::class);
        $serviceLocator->get(Security::class)->shouldNotBeCalled();

        $listener = new FirstArticleQualificationPlanValidationListener($serviceLocator->reveal());
        $listener->onPlanItemCreation(
            new ViewEvent(static::$kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, new \stdClass())
        );
    }

    /** @dataProvider bypassedHttpMethodProvider */
    public function testOnPlanItemCreationOnlyImpactsPut(string $method): void
    {
        $serviceLocator = $this->prophesize(ContainerInterface::class);
        $serviceLocator->get(Security::class)->shouldNotBeCalled();

        $request = new Request();
        $request->setMethod($method);

        $listener = new FirstArticleQualificationPlanValidationListener($serviceLocator->reveal());
        $listener->onPlanItemCreation(
            new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, new FirstArticleQualification())
        );
    }

    public function bypassedHttpMethodProvider(): iterable
    {
        yield [Request::METHOD_POST];
        yield [Request::METHOD_GET];
        yield [Request::METHOD_DELETE];
    }
}
