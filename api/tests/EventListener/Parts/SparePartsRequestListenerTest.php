<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Parts;

use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\TOCSparePartsRequest;
use App\Entity\Service\TechnicianOnCallType;
use App\EventListener\Parts\SparePartsRequestListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SparePartsRequestListenerTest extends TestCase
{
    use ProphecyTrait;

    private SparePartsRequestListener $listener;

    protected function setUp(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $this->listener = new SparePartsRequestListener($container->reveal());
    }

    public function testDoesNothingWhenNotPostMethod(): void
    {
        $dto = new TOCSparePartsRequest();
        $dto->type = TechnicianOnCallType::CUSTOMER;

        $request = new Request([], [], ['data' => $dto]);
        $request->setMethod('GET');

        $event = $this->prophesize(RequestEvent::class);
        $event->getRequest()->willReturn($request);

        $this->listener->afterSparePartsRequestDeserialize($event->reveal());

        self::assertSame(TechnicianOnCallType::CUSTOMER, $dto->type);
    }

    /**
     * @dataProvider provideMappingData
     */
    public function testMappingLogic(string $inputType, string $expectedType): void
    {
        $dto = new TOCSparePartsRequest();
        $dto->type = $inputType;

        $request = new Request([], [], ['data' => $dto]);
        $request->setMethod('POST');

        $event = $this->prophesize(RequestEvent::class);
        $event->getRequest()->willReturn($request);

        $this->listener->afterSparePartsRequestDeserialize($event->reveal());

        self::assertSame($expectedType, $dto->type);
    }

    public function provideMappingData(): array
    {
        return [
            [TechnicianOnCallType::NOT_DEFINE_YET, SparePartsRequest::TYPE_UNDEFINED],
            [TechnicianOnCallType::CUSTOMER, SparePartsRequest::TYPE_PAYABLE_SERVICES],
            [TechnicianOnCallType::SSO, SparePartsRequest::TYPE_SSO],
            [TechnicianOnCallType::FACTORY, SparePartsRequest::TYPE_WARRANTY],
            ['CUSTOM_TYPE', 'CUSTOM_TYPE'],
        ];
    }
}
