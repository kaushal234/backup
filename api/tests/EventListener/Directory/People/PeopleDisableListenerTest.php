<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Directory\People;

use App\Entity\Acl;
use App\Entity\AI\AILog;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\UserSetting;
use App\EventListener\Directory\People\PeopleDisableListener;
use App\Repository\AclRepository;
use App\Repository\AI\AILogRepository;
use App\Repository\UserSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PeopleDisableListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    private Filesystem $filesystem;
    private string $uploadDir;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->filesystem = new Filesystem();
        $this->uploadDir = sys_get_temp_dir().'/people_disable_listener_test_'.uniqid('', true);
        $this->filesystem->mkdir($this->uploadDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->uploadDir);
        parent::tearDown();
    }

    public function testDoesNothingForNonPeopleEntity(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Argument::any())->shouldNotBeCalled();

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent(new \stdClass(), Request::METHOD_PUT));
    }

    public function testDoesNothingForNonPutMethod(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Argument::any())->shouldNotBeCalled();

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent(new People(), Request::METHOD_POST));
    }

    public function testDoesNothingWhenUserWasAlreadyDisabled(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Argument::any())->shouldNotBeCalled();

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(true);

        $user = new People();
        $user->setDisabled(true);

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));
    }

    public function testDoesNothingWhenUserIsNotBeingDisabled(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Argument::any())->shouldNotBeCalled();

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(false);

        $user = new People();
        $user->setDisabled(false);

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));
    }

    public function testRemovesAclWhenUserIsDisabled(): void
    {
        $user = new People();
        $user->setDisabled(true);

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(false);

        $group = new Group();

        $aclRepo = $this->prophesize(AclRepository::class);
        $aclRepo->removeAclByGroup($user, $group)->shouldBeCalledOnce();

        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->method('findOneBy')->with(['name' => PeopleDisableListener::GROUP_AUTH_NAME])->willReturn($group);

        $logRepository = $this->createMock(AILogRepository::class);
        $logRepository->method('findFilePathsByPeople')->willReturn([]);
        $logRepository->expects($this->once())->method('deleteAllForPeople');

        $userSettingRepository = $this->createMock(UserSettingRepository::class);
        $userSettingRepository->expects($this->once())->method('deleteAllForPeople')->with($user);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Acl::class)->willReturn($aclRepo->reveal());
        $entityManager->getRepository(Group::class)->willReturn($groupRepository);
        $entityManager->getRepository(AILog::class)->willReturn($logRepository);
        $entityManager->getRepository(UserSetting::class)->willReturn($userSettingRepository);
        $entityManager->flush()->shouldBeCalledOnce();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->willReturn($entityManager->reveal());
        $container->get(Filesystem::class)->shouldNotBeCalled();

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));
    }

    public function testSkipsAclRemovalWhenGroupNotFound(): void
    {
        $user = new People();
        $user->setDisabled(true);

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(false);

        $aclRepo = $this->prophesize(AclRepository::class);
        $aclRepo->removeAclByGroup(Argument::any(), Argument::any())->shouldNotBeCalled();

        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->method('findOneBy')->with(['name' => PeopleDisableListener::GROUP_AUTH_NAME])->willReturn(null);

        $logRepository = $this->createMock(AILogRepository::class);
        $logRepository->method('findFilePathsByPeople')->willReturn([]);
        $logRepository->expects($this->once())->method('deleteAllForPeople');

        $userSettingRepository = $this->createMock(UserSettingRepository::class);
        $userSettingRepository->expects($this->once())->method('deleteAllForPeople');

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Acl::class)->willReturn($aclRepo->reveal());
        $entityManager->getRepository(Group::class)->willReturn($groupRepository);
        $entityManager->getRepository(AILog::class)->willReturn($logRepository);
        $entityManager->getRepository(UserSetting::class)->willReturn($userSettingRepository);
        $entityManager->flush()->shouldNotBeCalled();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->willReturn($entityManager->reveal());
        $container->get(Filesystem::class)->shouldNotBeCalled();

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));
    }

    public function testDeletesAiLogsAndPhysicalFilesWhenUserIsDisabled(): void
    {
        $file1 = 'ai/doc1.pdf';
        $file2 = 'ai/doc2.pdf';
        $this->filesystem->mkdir($this->uploadDir.'/ai');
        $this->filesystem->touch($this->uploadDir.'/'.$file1);
        $this->filesystem->touch($this->uploadDir.'/'.$file2);

        $this->assertFileExists($this->uploadDir.'/'.$file1);
        $this->assertFileExists($this->uploadDir.'/'.$file2);

        $user = new People();
        $user->setDisabled(true);

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(false);

        $aclRepo = $this->prophesize(AclRepository::class);
        $aclRepo->removeAclByGroup(Argument::any(), Argument::any())->shouldNotBeCalled();

        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->method('findOneBy')->willReturn(null);

        $logRepository = $this->createMock(AILogRepository::class);
        $logRepository->method('findFilePathsByPeople')->willReturn([$file1, $file2]);
        $logRepository->expects($this->once())->method('deleteAllForPeople');

        $userSettingRepository = $this->createMock(UserSettingRepository::class);
        $userSettingRepository->expects($this->once())->method('deleteAllForPeople');

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Acl::class)->willReturn($aclRepo->reveal());
        $entityManager->getRepository(Group::class)->willReturn($groupRepository);
        $entityManager->getRepository(AILog::class)->willReturn($logRepository);
        $entityManager->getRepository(UserSetting::class)->willReturn($userSettingRepository);
        $entityManager->flush()->shouldNotBeCalled();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->willReturn($entityManager->reveal());
        $container->get(Filesystem::class)->willReturn($this->filesystem);

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));

        $this->assertFileDoesNotExist($this->uploadDir.'/'.$file1);
        $this->assertFileDoesNotExist($this->uploadDir.'/'.$file2);
    }

    public function testDeletesAiLogsWithoutFilesWhenUserIsDisabled(): void
    {
        $user = new People();
        $user->setDisabled(true);

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(false);

        $aclRepo = $this->prophesize(AclRepository::class);
        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->method('findOneBy')->willReturn(null);

        $logRepository = $this->createMock(AILogRepository::class);
        $logRepository->method('findFilePathsByPeople')->willReturn([]);
        $logRepository->expects($this->once())->method('deleteAllForPeople');

        $userSettingRepository = $this->createMock(UserSettingRepository::class);
        $userSettingRepository->expects($this->once())->method('deleteAllForPeople');

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Acl::class)->willReturn($aclRepo->reveal());
        $entityManager->getRepository(Group::class)->willReturn($groupRepository);
        $entityManager->getRepository(AILog::class)->willReturn($logRepository);
        $entityManager->getRepository(UserSetting::class)->willReturn($userSettingRepository);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->willReturn($entityManager->reveal());
        $container->get(Filesystem::class)->shouldNotBeCalled();

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));
    }

    public function testDeletesUserSettingsWhenUserIsDisabled(): void
    {
        $user = new People();
        $user->setDisabled(true);

        $previousData = $this->prophesize(People::class);
        $previousData->isDisabled()->willReturn(false);

        $aclRepo = $this->prophesize(AclRepository::class);
        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->method('findOneBy')->willReturn(null);

        $logRepository = $this->createMock(AILogRepository::class);
        $logRepository->method('findFilePathsByPeople')->willReturn([]);
        $logRepository->expects($this->once())->method('deleteAllForPeople');

        $userSettingRepository = $this->createMock(UserSettingRepository::class);
        $userSettingRepository->expects($this->once())->method('deleteAllForPeople')->with($user);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Acl::class)->willReturn($aclRepo->reveal());
        $entityManager->getRepository(Group::class)->willReturn($groupRepository);
        $entityManager->getRepository(AILog::class)->willReturn($logRepository);
        $entityManager->getRepository(UserSetting::class)->willReturn($userSettingRepository);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->willReturn($entityManager->reveal());
        $container->get(Filesystem::class)->shouldNotBeCalled();

        $listener = $this->buildListener($container->reveal());
        $listener->postWrite($this->buildEvent($user, Request::METHOD_PUT, $previousData->reveal()));
    }

    private function buildListener(ContainerInterface $container): PeopleDisableListener
    {
        return new PeopleDisableListener($container, $this->uploadDir);
    }

    private function buildEvent(object $result, string $method, ?People $previousData = null): ViewEvent
    {
        $request = new Request();
        $request->setMethod($method);

        if (null !== $previousData) {
            $request->attributes->set('previous_data', $previousData);
        }

        return new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $result);
    }
}
