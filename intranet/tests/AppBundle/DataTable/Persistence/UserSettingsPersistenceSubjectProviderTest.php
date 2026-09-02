<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use ApiBundle\Model\BusinessUnit;
use ApiBundle\Model\User;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsPersistenceSubjectProviderTest extends TestCase
{
    public function testNoUser(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = new RequestStack();

        $token->expects($this->once())->method('getUser')->willReturn(null);
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $this->expectException(PersistenceSubjectNotFoundException::class);

        $subjectProvider = new UserSettingsPersistenceSubjectProvider($tokenStorage, $requestStack);
        $subjectProvider->provide();
    }

    public function testNoRequest(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = new RequestStack();

        $token->expects($this->once())->method('getUser')->willReturn($this->getUser());
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $this->expectException(PersistenceSubjectNotFoundException::class);

        $subjectProvider = new UserSettingsPersistenceSubjectProvider($tokenStorage, $requestStack);
        $subjectProvider->provide();
    }

    public function testDefault(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = $this->createMock(RequestStack::class);

        $token->expects($this->once())->method('getUser')->willReturn($this->getUser());
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn(new Request());

        $subjectProvider = new UserSettingsPersistenceSubjectProvider($tokenStorage, $requestStack);

        /** @var UserModulePersistenceSubjectAggregate $subject */
        $subject = $subjectProvider->provide();

        $this->assertSame('intranet', $subject->getModuleName());
    }

    public function testModuleName(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $request = new Request();
        $request->attributes->set('alvest_module', 'foo');

        $token->expects($this->once())->method('getUser')->willReturn($this->getUser());
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

        $subjectProvider = new UserSettingsPersistenceSubjectProvider($tokenStorage, $requestStack);

        /** @var UserModulePersistenceSubjectAggregate $subject */
        $subject = $subjectProvider->provide();

        $this->assertSame('foo', $subject->getModuleName());
    }

    public function getUser(): UserInterface
    {
        return new User(
            iriId: '/users/42',
            iriType: 'User',
            username: 'foo@bar.fr',
            firstname: 'foo',
            lastname: 'bar',
            disabled: false,
            hidden: false,
            expirationDate: '',
            photo: [],
            roles: [],
            acls: [],
            businessUnit: new BusinessUnit('bar', 'BusinessUnit', 9, 'foorbar'),
            token: 'tokentest'
        );
    }
}
