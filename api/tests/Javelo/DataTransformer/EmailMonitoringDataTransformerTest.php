<?php

declare(strict_types=1);

namespace App\Tests\Javelo\DataTransformer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Log;
use App\Entity\Directory\People;
use App\Javelo\DataTransformer\EmailMonitoringDataTransformer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EmailMonitoringDataTransformerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $iriConverter;
    private EmailMonitoringDataTransformer $transformer;

    protected function setUp(): void
    {
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->transformer = new EmailMonitoringDataTransformer($this->iriConverter->reveal());
    }

    public function testTransform()
    {
        $log = $this->prophesize(Log::class);
        $people = $this->prophesize(People::class);

        $log->getResource()->willReturn('/api/people/1');
        $log->getCreatedAt()->willReturn(new \DateTime('2023-06-18'));
        $log->getChangeSet()->willReturn([
            'userName' => ['previousUserName', 'UpdatedUserName'],
            'active' => [false, true],
        ]);
        $log->getAction()->willReturn('update');
        $log->getUser()->willReturn(null);

        $people->getId()->willReturn('1');
        $people->getJobTitle()->willReturn('Developer');
        $people->getUsername()->willReturn('UpdatedUserName');
        $people->getLastname()->willReturn('Doe');
        $people->getFirstname()->willReturn('John');
        $people->getLocale()->willReturn('en-US');
        $people->isDisabled()->willReturn(true);
        $people->getDepartment()->willReturn(null);
        $people->getBusinessUnit()->willReturn(null);
        $people->getSupervisor()->willReturn(null);
        $people->getGender()->willReturn(null);
        $people->getDisabledAt()->willReturn(null);
        $people->getPosition()->willReturn(null);
        $people->getContractType()->willReturn(null);
        $people->getCoefficient()->willReturn(null);

        $this->iriConverter->getResourceFromIri('/api/people/1')->willReturn($people->reveal());

        $expected = [
            'createdAt' => ['2023-06-18'],
            'intranetId' => [1],
            'action' => 'update',
            'poster' => null,
            'title' => ['Developer'],
            'userName' => ['previousUserName', 'UpdatedUserName'],  // updated by changes
            'familyName' => ['Doe'],
            'givenName' => ['John'],
            'locale' => ['en-US'],
            'active' => [false, true],                             // updated by changes
            'department' => [null],
            'businessUnit' => [null],
            'region' => [null],
            'subdivision' => [null],
            'division' => [null],
            'managerUserName' => [null],
            'gender' => [null],
            'contractType' => [null],
            'lastExitDate' => [null],
            'position' => [null],
            'workingTime' => [null],
        ];

        $result = $this->transformer->transform($log->reveal());
        $this->assertSame($expected, $result);
    }

    public function testTransformWithInvalidValue()
    {
        $this->assertNull($this->transformer->transform(new \stdClass()));
        $this->assertNull($this->transformer->transform(null));

        $log = new Log();
        $people = $log->setResource('iri d un people détruit en base');
        $this->iriConverter->getResourceFromIri($people->getResource())->willThrow(new ItemNotFoundException('pas trouvé'));
        $this->assertNull($this->transformer->transform($log));
    }
}
