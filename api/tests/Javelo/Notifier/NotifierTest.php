<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Notifier;

use App\Entity\Activity\Log;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Javelo\DataTransformer\EmailMonitoringDataTransformer;
use App\Javelo\Notifier\Notifier;
use App\Repository\Module\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class NotifierTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $mailer;
    private ObjectProphecy $dataTransformer;
    private MockObject $moduleRepository;
    private Notifier $notifier;

    protected function setUp(): void
    {
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->dataTransformer = $this->prophesize(EmailMonitoringDataTransformer::class);
        $this->moduleRepository = $this->getMockBuilder(ModuleRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBy'])->getMock();
        $this->notifier = new Notifier($this->mailer->reveal(), $this->dataTransformer->reveal(), $this->moduleRepository);
    }

    /**
     * @dataProvider provideEmailLists
     */
    public function testSendMonitoring(array $emailsByType, array $expectedEmailsToSend, bool $shouldBeSent): void
    {
        $log1 = $this->prophesize(Log::class);
        $log2 = $this->prophesize(Log::class);

        $this->dataTransformer->transform($log1->reveal())->willReturn(['transformedLog1']);
        $this->dataTransformer->transform($log2->reveal())->willReturn(['transformedLog2']);

        $module = $this->prophesize(Module::class);
        $moo = (new People())->setUsername($emailsByType['moo']);
        $module->getOperationalOwner()->shouldBeCalledOnce()->willReturn($moo);
        if (\array_key_exists('gku', $emailsByType)) {
            $gku = (new People())->setUsername($emailsByType['gku']);
            $module->getKeyUser()->shouldBeCalledOnce()->willReturn($gku);
        } else {
            $module->getKeyUser()->shouldBeCalledOnce()->willReturn(null);
        }

        if (\array_key_exists('lku', $emailsByType)) {
            $lku = new People();
            $lku->setUsername($emailsByType['lku']);

            $localKeysUsers = new ArrayCollection();
            $localKeysUsers->add($lku);
            $module->getLocalKeyUsers()->shouldBeCalledOnce()->willReturn($localKeysUsers);
        } else {
            $module->getLocalKeyUsers()->shouldBeCalledOnce()->willReturn(new ArrayCollection());
        }

        $this->moduleRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Javelo'])->willReturn($module->reveal());

        $expectedEmail = (new TemplatedEmail())
            ->subject('Test Subject')
            ->cc('devteam@tld-america.com')
            ->htmlTemplate('Emails/Javelo/monitoring.html.twig')
            ->context(['logs' => [['transformedLog1'], ['transformedLog2']]]);

        if ($expectedEmailsToSend) {
            $expectedEmail->to(...$expectedEmailsToSend);
        }

        $this->mailer->send($expectedEmail)->shouldBeCalledTimes($shouldBeSent ? 1 : 0);

        $this->notifier->sendMonitoring('Test Subject', [$log1->reveal(), $log2->reveal()]);
    }

    public function provideEmailLists(): array
    {
        return [
            'Email send with no gku' => [
                [
                    'moo' => 'devteam@tld-america.com',
                    'lku' => 'ouiouioui@tld-america.com',
                ],
                ['devteam@tld-america.com', 'ouiouioui@tld-america.com'],
                true,
            ],
            'Email send with no lku' => [
                [
                    'moo' => 'devteam@tld-america.com',
                    'gku' => 'ouiouioui@tld-america.com',
                ],
                ['devteam@tld-america.com', 'ouiouioui@tld-america.com'],
                true,
            ],
            'Email send with all emails' => [
                [
                    'moo' => 'devteam@tld-america.com',
                    'gku' => 'nanan@tld-america.com',
                    'lku' => 'ouiouioui@tld-america.com',
                ],
                ['devteam@tld-america.com', 'nanan@tld-america.com', 'ouiouioui@tld-america.com'],
                true,
            ],
            'Email send with all emails except empty' => [
                [
                    'moo' => 'devteam@tld-america.com',
                    'gku' => '',
                    'lku' => 'ouiouioui@tld-america.com',
                ],
                ['devteam@tld-america.com', 'ouiouioui@tld-america.com'],
                true,
            ],
            'Email send with all emails except null' => [
                [
                    'moo' => 'devteam@tld-america.com',
                    'gku' => 'ouiouioui@tld-america.com',
                    'lku' => null,
                ],
                ['devteam@tld-america.com', 'ouiouioui@tld-america.com'],
                true,
            ],
            'One email used' => [
                [
                    'moo' => 'devteam@tld-america.com',
                    'gku' => '',
                    'lku' => '',
                ],
                ['devteam@tld-america.com'],
                true,
            ],
        ];
    }

    public function testSendMonitoringNoUsersException(): void
    {
        $module = $this->prophesize(Module::class);
        $module->getOperationalOwner()->shouldBeCalledOnce()->willReturn(null);
        $module->getKeyUser()->shouldBeCalledOnce()->willReturn(null);
        $module->getLocalKeyUsers()->shouldBeCalledOnce()->willReturn(new ArrayCollection());

        $this->moduleRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Javelo'])->willReturn($module->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No user to send monitoring email');

        $this->notifier->sendMonitoring('Test Subject', []);
    }

    public function testSendMonitoringInvalidModuleException(): void
    {
        $this->moduleRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Javelo'])->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid module');

        $this->notifier->sendMonitoring('Test Subject', []);
    }

    public function testSendMonitoringWithNullTransformedLog(): void
    {
        $log1 = $this->prophesize(Log::class);
        $log2 = $this->prophesize(Log::class);
        $log3 = $this->prophesize(Log::class);

        $transformedLog1 = ['transformedLog1'];
        $transformedLog2 = null;
        $transformedLog3 = ['transformedLog3'];

        $this->dataTransformer->transform($log1->reveal())->willReturn($transformedLog1);
        $this->dataTransformer->transform($log2->reveal())->willReturn($transformedLog2);
        $this->dataTransformer->transform($log3->reveal())->willReturn($transformedLog3);

        $module = $this->prophesize(Module::class);
        $moo = (new People())->setUsername('devteam@tld-america.com');
        $module->getOperationalOwner()->shouldBeCalledOnce()->willReturn($moo);

        $gku = (new People())->setUsername('gku@tld-america.com');
        $module->getKeyUser()->shouldBeCalledOnce()->willReturn($gku);

        $lku = (new People())->setUsername('lku@tld-america.com');

        $localKeysUsers = new ArrayCollection();
        $localKeysUsers->add($lku);
        $module->getLocalKeyUsers()->shouldBeCalledOnce()->willReturn($localKeysUsers);

        $this->moduleRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Javelo'])->willReturn($module->reveal());

        $expectedEmail = (new TemplatedEmail())
            ->to('devteam@tld-america.com', 'gku@tld-america.com', 'lku@tld-america.com')
            ->cc('devteam@tld-america.com')
            ->subject('Test Subject')
            ->htmlTemplate('Emails/Javelo/monitoring.html.twig')
            ->context([
                'logs' => [
                    $transformedLog1,
                    $transformedLog3,
                ],
            ]);

        $this->mailer->send(Argument::that(function (TemplatedEmail $email) use ($expectedEmail, $transformedLog1, $transformedLog2, $transformedLog3) {
            array_map(static fn ($address) => $address->getAddress(), $expectedEmail->getTo());
            array_map(static fn ($address) => $address->getAddress(), $email->getTo());
            $this->assertSame(
                array_map(static fn ($address) => $address->getAddress(), $expectedEmail->getTo()),
                array_map(static fn ($address) => $address->getAddress(), $email->getTo())
            );
            $this->assertSame($expectedEmail->getHtmlTemplate(), $email->getHtmlTemplate());
            $this->assertNotContains($transformedLog2, $email->getContext()['logs']);
            $this->assertContains($transformedLog1, $email->getContext()['logs']);
            $this->assertContains($transformedLog3, $email->getContext()['logs']);

            return true;
        }))->shouldBeCalledOnce();

        $this->notifier->sendMonitoring('Test Subject', [$log1->reveal(), $log2->reveal(), $log3->reveal()]);
    }

    public function testSendCreationGroupsRequest(): void
    {
        $module = $this->prophesize(Module::class);
        $moo = (new People())->setUsername('devteam@tld-america.com');
        $module->getOperationalOwner()->shouldBeCalledOnce()->willReturn($moo);
        $module->getKeyUser()->shouldBeCalledOnce()->willReturn(null);
        $module->getLocalKeyUsers()->shouldBeCalledOnce()->willReturn(new ArrayCollection());

        $this->moduleRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Javelo'])->willReturn($module->reveal());

        $missingGroups = [
            'BU_SALES' => ['businessUnit' => 'Sales', 'division' => 'EMEA'],
            'BU_MARKETING' => ['businessUnit' => 'Marketing', 'division' => 'EMEA'],
            'BU_HR' => ['businessUnit' => 'HR', 'division' => null],
        ];

        $expectedMissingGroupsByDivision = [
            'EMEA' => [
                'BU_SALES' => 'Sales',
                'BU_MARKETING' => 'Marketing',
            ],
            'N/A' => [
                'BU_HR' => 'HR',
            ],
        ];

        $this->mailer->send(Argument::that(function (TemplatedEmail $email) use ($expectedMissingGroupsByDivision) {
            $this->assertSame($expectedMissingGroupsByDivision, $email->getContext()['missingGroupsByDivision']);
            $this->assertSame('Create Business Unit Group on Javelo', $email->getSubject());
            $this->assertSame('Emails/Javelo/create_business_unit_group_request.html.twig', $email->getHtmlTemplate());

            return true;
        }))->shouldBeCalledOnce();

        $this->notifier->sendCreationGroupsRequest($missingGroups);
    }

    public function testSendCreationGroupsRequestNoUsersException(): void
    {
        $module = $this->prophesize(Module::class);
        $module->getOperationalOwner()->shouldBeCalledOnce()->willReturn(null);
        $module->getKeyUser()->shouldBeCalledOnce()->willReturn(null);
        $module->getLocalKeyUsers()->shouldBeCalledOnce()->willReturn(new ArrayCollection());

        $this->moduleRepository->expects($this->once())->method('findOneBy')->with(['name' => 'Javelo'])->willReturn($module->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No user to send creation group request email');

        $this->notifier->sendCreationGroupsRequest(['BU_SALES' => ['businessUnit' => 'Sales', 'division' => 'EMEA']]);
    }
}
