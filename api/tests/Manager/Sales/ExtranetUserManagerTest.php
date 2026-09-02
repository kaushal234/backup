<?php

declare(strict_types=1);

namespace App\Tests\Manager\Sales;

use App\Entity\Country;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\Network;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Module\Module;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use App\Entity\Sales\SalesArea;
use App\Entity\User;
use App\Manager\Sales\ExtranetUserManager;
use App\Manager\UserManager;
use App\Notifier\Directory\PeopleExtranetAccessNotifier;
use App\Notifier\Tasks\SequenceNotifier;
use App\Repository\Directory\PeopleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Manager\TaskCommentsManager;
use LegacyBundle\Model\Sequence;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtranetUserManagerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testWithPeopleAsParameter()
    {
        $peopleExtranetAccessNotifierProphecy = $this->prophesize(PeopleExtranetAccessNotifier::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['findByEmailOrUsername'])->getMock();
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $taskCommentManager = $this->prophesize(TaskCommentsManager::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $extranetUser = new ExtranetUser();
        $extranetUser->setEmail('relou@tld.fr');
        $supervisor = new People();
        $supervisor->setEmail('super-relou@tld.fr');
        $people = new People();
        $people->setSupervisor($supervisor)->setEmail('relou@tld.fr')->setDisabled(false);

        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryMock);
        $userRepositoryMock->expects($this->once())->method('findByEmailOrUsername')->with('relou@tld.fr')->willReturn([$people]);
        $routerProphecy->generate(Argument::any())->shouldNotBeCalled();
        $peopleExtranetAccessNotifierProphecy->sendEmail($people)->shouldBeCalledTimes(1);
        $sequenceManagerProphecy->findOpenSequence(Argument::cetera())->shouldNotBeCalled();
        $sequenceManagerProphecy->insert(Argument::any())->shouldNotBeCalled();
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();

        $manager = new ExtranetUserManager($userManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceManagerProphecy->reveal(), $validatorProphecy->reveal(), $taskCommentManager->reveal(), $routerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $peopleExtranetAccessNotifierProphecy->reveal(), $loggerProphecy->reveal());

        $manager->handleExtranetUserRequest($extranetUser);
    }

    public function testWithExistingEnableXuAsParameter()
    {
        $peopleExtranetAccessNotifierProphecy = $this->prophesize(PeopleExtranetAccessNotifier::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['findByEmailOrUsername'])->getMock();
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $taskCommentManager = $this->prophesize(TaskCommentsManager::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryMock);
        $extranetUser = new ExtranetUser();
        $extranetUser->setEmail('relou@tld.fr')->setDisabled(false);

        $userRepositoryMock->expects($this->once())->method('findByEmailOrUsername')->with('relou@tld.fr')->willReturn([$extranetUser]);
        $routerProphecy->generate(Argument::any())->shouldNotBeCalled();
        $sequenceManagerProphecy->findOpenSequence(Argument::cetera())->shouldNotBeCalled();
        $sequenceManagerProphecy->insert(Argument::any())->shouldNotBeCalled();
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();
        $userManagerProphecy->resetPasswordConfirmation($extranetUser)->shouldBeCalledTimes(1);

        $manager = new ExtranetUserManager($userManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceManagerProphecy->reveal(), $validatorProphecy->reveal(), $taskCommentManager->reveal(), $routerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $peopleExtranetAccessNotifierProphecy->reveal(), $loggerProphecy->reveal());

        $manager->handleExtranetUserRequest($extranetUser);
    }

    public function testWithExistingDisableXuWithOpenSequenceAsParameter()
    {
        $peopleExtranetAccessNotifierProphecy = $this->prophesize(PeopleExtranetAccessNotifier::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $taskCommentManagerProphecy = $this->prophesize(TaskCommentsManager::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['findByEmailOrUsername'])->getMock();
        $notifierProphecy = $this->prophesize(SequenceNotifier::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $extranetUser = new ExtranetUser();
        $extranetUser
            ->setExtranetUserProfile(new ExtranetUserProfile())
            ->setLegacyId(12)
            ->setEmail('relou@tld.fr');

        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryMock);
        $userRepositoryMock->expects($this->once())->method('findByEmailOrUsername')->with('relou@tld.fr')->willReturn([$extranetUser]);
        $sequenceManagerProphecy->findOpenSequence('sales.extranetuser.approval', 12)->shouldBeCalledTimes(1)->willReturn(['id' => 56]);
        $entityManagerProphecy->getRepository(SalesArea::class)->shouldNotBeCalled();
        $entityManagerProphecy->getRepository(Module::class)->shouldNotBeCalled();
        $routerProphecy->generate(Argument::any())->shouldNotBeCalled();
        $sequenceManagerProphecy->insert(Argument::any())->shouldNotBeCalled();
        $notifierProphecy->sendEmail(Argument::cetera())->shouldNotBeCalled();

        $manager = new ExtranetUserManager($userManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceManagerProphecy->reveal(), $validatorProphecy->reveal(), $taskCommentManagerProphecy->reveal(), $routerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $peopleExtranetAccessNotifierProphecy->reveal(), $loggerProphecy->reveal());

        $manager->handleExtranetUserRequest($extranetUser);
    }

    public function testWithExistingDisableXuAsParameter()
    {
        $peopleExtranetAccessNotifierProphecy = $this->prophesize(PeopleExtranetAccessNotifier::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $taskCommentManager = $this->prophesize(TaskCommentsManager::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['findByEmailOrUsername'])->getMock();
        $moduleRepositoryMock = $this->createMock(EntityRepository::class);
        $salesAreaRepositoryMock = $this->createMock(EntityRepository::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $extranetUser = new ExtranetUser();
        $extranetUser
            ->setExtranetUserProfile(new ExtranetUserProfile())
            ->setLegacyId(12)
            ->setEmail('relou@tld.fr');

        $owner = new People();
        $owner
            ->setBusinessUnit((new BusinessUnit())->setLocation(new Location()))
            ->setEmail('jean-paul.delaite@tld-group.com');

        $module = (new Module())
            ->setName('XU')
            ->setOperationalOwner($owner);

        $routerProphecy->generate('extranet_users', ['id' => null], Router::ABSOLUTE_URL)->shouldBeCalledTimes(1);
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryMock);
        $entityManagerProphecy->getRepository(SalesArea::class)->shouldBeCalledTimes(1)->willReturn($salesAreaRepositoryMock);
        $entityManagerProphecy->getRepository(Module::class)->shouldBeCalledTimes(1)->willReturn($moduleRepositoryMock);
        $entityManagerProphecy->persist($extranetUser)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);
        $userRepositoryMock->expects($this->once())->method('findByEmailOrUsername')->with('relou@tld.fr')->willReturn([$extranetUser]);
        $moduleRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'XU'])->willReturn($module);
        $validatorProphecy->validate($extranetUser)->shouldBeCalledTimes(1)->willReturn(new ConstraintViolationList());
        $sequenceManagerProphecy->findOpenSequence('sales.extranetuser.approval', 12)->shouldBeCalledTimes(1)->willReturn(false);
        $sequenceManagerProphecy->insert(Argument::type(Sequence::class))->shouldBeCalledTimes(1);
        $sequenceNotifierProphecy->sendEmail(Argument::type(Sequence::class))->shouldBeCalledTimes(1);

        $manager = new ExtranetUserManager($userManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceManagerProphecy->reveal(), $validatorProphecy->reveal(), $taskCommentManager->reveal(), $routerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $peopleExtranetAccessNotifierProphecy->reveal(), $loggerProphecy->reveal());

        $manager->handleExtranetUserRequest($extranetUser);
    }

    public function testWithNewXuAsParameter()
    {
        $peopleExtranetAccessNotifierProphecy = $this->prophesize(PeopleExtranetAccessNotifier::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $taskCommentManager = $this->prophesize(TaskCommentsManager::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['findByEmailOrUsername'])->getMock();
        $moduleRepositoryMock = $this->createMock(EntityRepository::class);
        $salesAreaRepositoryMock = $this->createMock(EntityRepository::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $extranetUser = new ExtranetUser();
        $extranetUser
            ->setExtranetUserProfile(new ExtranetUserProfile())
            ->setLegacyId(12)
            ->setEmail('doudou@tld.fr');

        $owner = new People();
        $owner
            ->setBusinessUnit((new BusinessUnit())->setLocation(new Location()))
            ->setEmail('jean-paul.delaite@tld-group.com');

        $module = (new Module())
            ->setName('XU')
            ->setOperationalOwner($owner);

        $routerProphecy->generate('extranet_users', ['id' => null], Router::ABSOLUTE_URL)->shouldBeCalledTimes(1);
        $moduleRepositoryMock->expects($this->once())->method('findOneBy')->with(['name' => 'XU'])->willReturn($module);
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryMock);
        $entityManagerProphecy->getRepository(SalesArea::class)->shouldBeCalledTimes(1)->willReturn($salesAreaRepositoryMock);
        $entityManagerProphecy->getRepository(Module::class)->shouldBeCalledTimes(1)->willReturn($moduleRepositoryMock);
        $entityManagerProphecy->persist($extranetUser)->shouldBeCalledTimes(2);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(2);
        $userRepositoryMock->expects($this->once())->method('findByEmailOrUsername')->with('doudou@tld.fr')->willReturn([]);
        $validatorProphecy->validate($extranetUser)->shouldBeCalledTimes(1)->willReturn(new ConstraintViolationList());
        $sequenceManagerProphecy->findOpenSequence('sales.extranetuser.approval', 12)->shouldBeCalledTimes(1)->willReturn(false);
        $sequenceManagerProphecy->insert(Argument::that(static fn (Sequence $sequence) => 'jean-paul.delaite@tld-group.com' === $sequence->getAssignee()->getEmail()))->shouldBeCalledTimes(1);
        $sequenceNotifierProphecy->sendEmail(Argument::type(Sequence::class))->shouldBeCalledTimes(1);

        $manager = new ExtranetUserManager($userManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceManagerProphecy->reveal(), $validatorProphecy->reveal(), $taskCommentManager->reveal(), $routerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $peopleExtranetAccessNotifierProphecy->reveal(), $loggerProphecy->reveal());

        $manager->handleExtranetUserRequest($extranetUser);
    }

    public function testSequenceIsSentToRightAssigneeAndCommentIsInsertedForViolations()
    {
        $peopleExtranetAccessNotifierProphecy = $this->prophesize(PeopleExtranetAccessNotifier::class);
        $sequenceNotifierProphecy = $this->prophesize(SequenceNotifier::class);
        $userManagerProphecy = $this->prophesize(UserManager::class);
        $sequenceManagerProphecy = $this->prophesize(SequenceManager::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->onlyMethods(['findByEmailOrUsername'])->getMock();
        $salesAreaRepositoryMock = $this->createMock(EntityRepository::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findGroupMembers'])->getMock();
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $taskCommentManager = $this->prophesize(TaskCommentsManager::class);
        $routerProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $location = (new Location())->setErp(42)->setNetwork((new Network())->setName('TLD'));
        $assignee = (new People())->setEmail('assignee@tld.fr');
        $asm = (new People())->setBusinessUnit((new BusinessUnit())->setLocation($location));
        $country = (new Country());
        $extranetUserProfile = (new ExtranetUserProfile());
        $extranetUserProfile->country = $country;

        $extranetUser = new ExtranetUser();
        $extranetUser
            ->setExtranetUserProfile($extranetUserProfile)
            ->addPhone((new Phone())->setType('phone')->setNumber('not_a_number'))
            ->setLegacyId(12)
            ->setEmail('doudou@tld.fr');

        $routerProphecy->generate('extranet_users', ['id' => null], Router::ABSOLUTE_URL)->shouldBeCalledTimes(1);
        $salesAreaRepositoryMock->expects($this->once())->method('findBy')->with(['country' => $country])->willReturn([(new SalesArea())->setSso($location)->setAsm($asm)]);
        $userRepositoryMock->expects($this->once())->method('findByEmailOrUsername')->with($extranetUser->getEmail())->willReturn([]);
        $peopleRepositoryMock->expects($this->once())->method('findGroupMembers')->with('ROLE_SXU', $location)->willReturn([$assignee]);
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryMock);
        $entityManagerProphecy->getRepository(SalesArea::class)->shouldBeCalledTimes(1)->willReturn($salesAreaRepositoryMock);
        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);
        $entityManagerProphecy->persist($extranetUser)->shouldBeCalledTimes(2);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(2);
        $sequenceManagerProphecy->findOpenSequence('sales.extranetuser.approval', 12)->shouldBeCalledTimes(1)->willReturn(false);

        $constraintViolationList = new ConstraintViolationList();
        $violation = new ConstraintViolation('Pas bien', null, [], 'Toto', 'poumons', 'Le Tabac');
        $constraintViolationList->add($violation);

        $validatorProphecy->validate($extranetUser)->shouldBeCalledTimes(1)->willReturn($constraintViolationList);
        $taskCommentManager->insertComment(Argument::that(static fn (Sequence $sequence) => 'assignee@tld.fr' === $sequence->getAssignee()->getEmail()), "Please make sure to update the following before ANY action on this sequence: \n - Le Tabac: Pas bien")->shouldBeCalledTimes(1);
        $sequenceManagerProphecy->insert(Argument::type(Sequence::class))->shouldBeCalledTimes(1);
        $sequenceNotifierProphecy->sendEmail(Argument::type(Sequence::class))->shouldBeCalledTimes(1);

        $manager = new ExtranetUserManager($userManagerProphecy->reveal(), $entityManagerProphecy->reveal(), $sequenceManagerProphecy->reveal(), $validatorProphecy->reveal(), $taskCommentManager->reveal(), $routerProphecy->reveal(), $sequenceNotifierProphecy->reveal(), $peopleExtranetAccessNotifierProphecy->reveal(), $loggerProphecy->reveal());
        $manager->handleExtranetUserRequest($extranetUser);
    }
}
