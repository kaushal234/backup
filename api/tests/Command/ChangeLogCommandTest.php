<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ChangeLogCommand;
use App\Entity\Directory\People;
use App\Entity\Module\ChangeLog;
use App\Entity\Module\Module;
use App\Http\GitlabClient;
use App\Mailer\Module\ChangelogPoolMailer;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Module\ModuleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChangeLogCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:changelog:synchronize';

    /** @var GitlabClient|MockObject */
    private $client;

    /** @var ManagerRegistry|MockObject */
    private $registry;

    /**
     * @dataProvider commitProvider
     */
    public function testCommitScenarios(
        array $commit,
        ?Module $module,
        ?People $people,
        ?ChangeLog $existingLog,
        callable $repositoryExpectations,
        callable $mailerExpectations
    ) {
        $this->client = $this->getMockBuilder(GitlabClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->client->method('getCommits')->willReturn([$commit]);

        $this->registry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->method('getManager')->willReturn($manager);

        // Repositories
        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->getMock();
        $moduleRepository = $this->getMockBuilder(ModuleRepository::class)->disableOriginalConstructor()->getMock();
        $changeLogRepository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();

        // Apply expectations (custom per test case)
        $repositoryExpectations($peopleRepository, $moduleRepository, $changeLogRepository, $people, $module, $existingLog);

        $manager->expects(self::exactly(3))
            ->method('getRepository')
            ->withConsecutive([People::class], [Module::class], [ChangeLog::class])
            ->willReturnOnConsecutiveCalls($peopleRepository, $moduleRepository, $changeLogRepository);

        // Mailer
        $mailerProphecy = $this->prophesize(ChangelogPoolMailer::class);
        $mailerExpectations($mailerProphecy);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(
            new ChangeLogCommand($this->client, $this->registry, $mailerProphecy->reveal())
        );

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
            'repositoryIds' => [12],
        ]);
    }

    public function commitProvider(): array
    {
        $validCommit = [
            'id' => '8cfc6318a3ef8b25e7be57a3b4b3b7e4aaecfce5',
            'created_at' => '2026-02-25T11:14:49.000+05:30',
            'message' => "fix(2RE):\nModule: 2RE\nDescription: Fake Description\nRefs: SP#12111, TTS#44135\n",
            'author_email' => 'user-superuser@tld.fr',
        ];

        return [
            'valid commit' => [
                $validCommit,
                (new Module())->setName('2RE'),
                (new People())->setEmail('user-superuser@tld.fr'),
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo, $people, $module) {
                    $peopleRepo->expects(self::once())
                        ->method('findOneBy')
                        ->willReturn($people);

                    $moduleRepo->expects(self::once())
                        ->method('findByName')
                        ->willReturn($module);

                    $logRepo->expects(self::once())
                        ->method('findOneBy')
                        ->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::type(ChangeLog::class))->shouldBeCalledTimes(1);
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledTimes(1);
                },
            ],
            'description key with space before colon' => [
                str_replace('Description: Fake Description', 'Description : Fake Description', $validCommit),
                (new Module())->setName('2RE'),
                (new People())->setEmail('user-superuser@tld.fr'),
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo, $people, $module) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn($people);
                    $moduleRepo->expects(self::once())->method('findByName')->willReturn($module);
                    $logRepo->expects(self::once())->method('findOneBy')->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldBeCalledOnce();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],
            'module key with space before colon' => [
                str_replace('Module: 2RE', 'Module : 2RE', $validCommit),
                (new Module())->setName('2RE'),
                (new People())->setEmail('user-superuser@tld.fr'),
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo, $people, $module) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn($people);
                    $moduleRepo->expects(self::once())->method('findByName')->willReturn($module);
                    $logRepo->expects(self::once())->method('findOneBy')->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldBeCalledOnce();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],
            'refs key with space before colon' => [
                str_replace('Refs: SP#12111, TTS#44135', 'Refs : SP#12111, TTS#44135', $validCommit),
                (new Module())->setName('2RE'),
                (new People())->setEmail('user-superuser@tld.fr'),
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo, $people, $module) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn($people);
                    $moduleRepo->expects(self::once())->method('findByName')->willReturn($module);
                    $logRepo->expects(self::once())->method('findOneBy')->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldBeCalledOnce();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],
            'no module' => [
                str_replace('Module: 2RE', 'Module:', $validCommit),
                null,
                (new People())->setEmail('user-superuser@tld.fr'),
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo, $people) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn($people);
                    $moduleRepo->expects(self::once())->method('findByName')->willReturn(null);
                    $logRepo->expects(self::once())->method('findOneBy')->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::type(ChangeLog::class))->shouldBeCalledTimes(1);
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledTimes(1);
                },
            ],
            'unknown module' => [
                str_replace('Module: 2RE', 'Module:Unknown', $validCommit),
                null,
                (new People())->setEmail('user-superuser@tld.fr'),
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo, $people) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn($people);
                    $moduleRepo->expects(self::once())->method('findByName')->willReturn(null);
                    $logRepo->expects(self::once())->method('findOneBy')->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::type(ChangeLog::class))->shouldBeCalledTimes(1);
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledTimes(1);
                },
            ],
            'no description' => [
                str_replace('Description: Fake Description', 'Description:', $validCommit),
                null,
                null,
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo) {
                    $peopleRepo->expects(self::never())->method('findOneBy');
                    $moduleRepo->expects(self::never())->method('findByName');
                    $logRepo->expects(self::never())->method('findOneBy');
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldNotBeCalled();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],
            'no description key' => [
                str_replace('Description: Fake Description', '', $validCommit),
                null,
                null,
                null,
                static function ($peopleRepo, $moduleRepo, $logRepo) {
                    $peopleRepo->expects(self::never())->method('findOneBy');
                    $moduleRepo->expects(self::never())->method('findByName');
                    $logRepo->expects(self::never())->method('findOneBy');
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldNotBeCalled();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],
            'no user' => [
                $validCommit,
                null,
                null,
                null,
                static function ($peopleRepo) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn(null);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldNotBeCalled();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],

            'existing hash' => [
                $validCommit,
                (new Module())->setName('2RE'),
                (new People())->setEmail('user-superuser@tld.fr'),
                new ChangeLog(),
                static function ($peopleRepo, $moduleRepo, $logRepo, $people, $module, $existingLog) {
                    $peopleRepo->expects(self::once())->method('findOneBy')->willReturn($people);
                    $moduleRepo->expects(self::once())->method('findByName')->willReturn($module);
                    $logRepo->expects(self::once())->method('findOneBy')->willReturn($existingLog);
                },
                static function ($mailer) {
                    $mailer->addChangelog(Argument::any())->shouldNotBeCalled();
                    $mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig')->shouldBeCalledOnce();
                },
            ],
        ];
    }

    /**
     * @dataProvider commitMessageProvider
     */
    public function testCommitPattern(string $message, ?array $expected): void
    {
        $normalizedMessage = preg_replace("/\r|\n/", '', $message);

        $matches = [];
        $result = preg_match_all(
            ChangeLogCommand::COMMIT_PATTERN,
            $normalizedMessage,
            $matches,
            \PREG_SET_ORDER
        );

        if (null === $expected) {
            self::assertSame(0, $result);

            return;
        }

        self::assertSame(1, $result);

        $infos = $matches[0];

        self::assertSame($expected['type'], $infos['type']);
        self::assertSame($expected['scope'], $infos['scope']);
        self::assertSame($expected['module'], $infos['module']);
        self::assertSame($expected['description'], mb_trim($infos['description']));
        self::assertSame($expected['refs'], mb_trim($infos['refs'] ?? ''));
    }

    public function commitMessageProvider(): array
    {
        return [
            'valid full commit' => [
                "fix(api): something\nModule: 2RE\nDescription: Fix bug\nRefs: TTS#123",
                [
                    'type' => 'fix',
                    'scope' => 'api',
                    'module' => '2RE',
                    'description' => 'Fix bug',
                    'refs' => 'TTS#123',
                ],
            ],
            'description key with space before colon' => [
                "fix(api): something\nModule: 2RE\nDescription : Fix bug\nRefs: TTS#123",
                [
                    'type' => 'fix',
                    'scope' => 'api',
                    'module' => '2RE',
                    'description' => 'Fix bug',
                    'refs' => 'TTS#123',
                ],
            ],
            'refs key with space before colon' => [
                "fix(api): something\nModule: 2RE\nDescription : Fix bug\nRefs : TTS#123",
                [
                    'type' => 'fix',
                    'scope' => 'api',
                    'module' => '2RE',
                    'description' => 'Fix bug',
                    'refs' => 'TTS#123',
                ],
            ],
            'module key with space before colon' => [
                "fix(api): something\nModule : 2RE\nDescription : Fix bug\nRefs : TTS#123",
                [
                    'type' => 'fix',
                    'scope' => 'api',
                    'module' => '2RE',
                    'description' => 'Fix bug',
                    'refs' => 'TTS#123',
                ],
            ],
            'no scope' => [
                "feat: something\nModule: CORE\nDescription: Add feature\nRefs: TTS#999",
                [
                    'type' => 'feat',
                    'scope' => '',
                    'module' => 'CORE',
                    'description' => 'Add feature',
                    'refs' => 'TTS#999',
                ],
            ],

            'no module' => [
                "fix(test):\nModule:\nDescription: No module here\nRefs: TTS#111",
                [
                    'type' => 'fix',
                    'scope' => 'test',
                    'module' => '',
                    'description' => 'No module here',
                    'refs' => 'TTS#111',
                ],
            ],

            'no refs' => [
                "fix(abc):\nModule: ABC\nDescription: Something",
                [
                    'type' => 'fix',
                    'scope' => 'abc',
                    'module' => 'ABC',
                    'description' => 'Something',
                    'refs' => '',
                ],
            ],

            'empty description' => [
                "fix(abc):\nModule: ABC\nDescription:\nRefs: TTS#123",
                [
                    'type' => 'fix',
                    'scope' => 'abc',
                    'module' => 'ABC',
                    'description' => '',
                    'refs' => 'TTS#123',
                ],
            ],
            'without description key' => [
                "fix(abc):\nModule: ABC\n\nRefs: TTS#123",
                null,
            ],

            'invalid format (no type)' => [
                'random text without pattern',
                null,
            ],

            'complex refs' => [
                "fix(core):\nModule: CORE\nDescription: Multi refs\nRefs: SP#1, TTS#555, ABC#99",
                [
                    'type' => 'fix',
                    'scope' => 'core',
                    'module' => 'CORE',
                    'description' => 'Multi refs',
                    'refs' => 'SP#1, TTS#555, ABC#99',
                ],
            ],
            'big body description' => [
                "fix(core): Some Message\n Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo\n
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo\n
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo\n
                Module: CORE\nDescription: Multi refs\nRefs: SP#1, TTS#555, ABC#99",
                [
                    'type' => 'fix',
                    'scope' => 'core',
                    'module' => 'CORE',
                    'description' => 'Multi refs',
                    'refs' => 'SP#1, TTS#555, ABC#99',
                ],
            ],
        ];
    }
}
