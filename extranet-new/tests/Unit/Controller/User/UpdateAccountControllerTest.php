<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\User;

use App\Controller\User\UpdateAccountController;
use App\CQRS\Command\User\UpdateUserCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\User\FindUserQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\User\UpdateUser;
use App\Http\Responder;
use App\Sdk\Resource\User as UserResource;
use App\Security\User\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * @group unit
 *
 * @see UpdateAccountController
 */
final class UpdateAccountControllerTest extends TestCase
{
    public function testEmailIsPrePopulatedFromApiUserBeforeFormHandling(): void
    {
        $apiUser = $this->makeApiUser('api@example.com');

        $queryBus = $this->createMock(QueryBusInterface::class);
        $queryBus->method('dispatch')
            ->with($this->isInstanceOf(FindUserQuery::class))
            ->willReturn($apiUser);

        $capturedDto = null;
        /** @var FormInterface&\PHPUnit\Framework\MockObject\MockObject $form */
        $form = $this->makeForm(submitted: true, valid: true);
        $form->method('getData')->willReturnCallback(static fn () => $capturedDto);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturnCallback(
            static function ($type, UpdateUser $dto) use ($form, &$capturedDto) {
                $capturedDto = $dto;

                return $form;
            }
        );

        $commandBus = $this->createMock(CommandBusInterface::class);
        $commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UpdateUserCommand::class));

        $currentUser = $this->makeSecurityUser();

        $controller = new UpdateAccountController($this->makeResponder(), $formFactory, $commandBus, $queryBus);
        $controller->__invoke($currentUser, new Request());

        $this->assertNotNull($capturedDto);
        $this->assertSame('/people/1', $capturedDto->iri);
        $this->assertSame('/profile/1', $capturedDto->profileIri);
    }

    public function testInvalidFormDoesNotDispatchCommand(): void
    {
        $queryBus = $this->createMock(QueryBusInterface::class);
        $queryBus->method('dispatch')->willReturn($this->makeApiUser());

        $form = $this->makeForm(submitted: true, valid: false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturn($form);

        $commandBus = $this->createMock(CommandBusInterface::class);
        $commandBus->expects($this->never())->method('dispatch');

        $currentUser = $this->makeSecurityUser();

        $controller = new UpdateAccountController($this->makeResponder(), $formFactory, $commandBus, $queryBus);
        $controller->__invoke($currentUser, new Request());
    }

    private function makeSecurityUser(): User
    {
        return new User(id: 1, firstname: 'John', lastname: 'Doe', identifier: 'john@example.com', token: 'tok', passwordExpirationDate: '2099-01-01', language: 'en');
    }

    private function makeApiUser(string $email = 'user@example.com'): UserResource
    {
        return new UserResource(
            iri: '/people/1',
            id: 1,
            profileIri: '/profile/1',
            lastname: 'Doe',
            firstname: 'John',
            email: $email,
        );
    }

    private function makeResponder(): Responder
    {
        $flashBag = $this->createMock(FlashBagInterface::class);

        $session = $this->createMock(Session::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $httpRequest = new Request();
        $httpRequest->setSession($session);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($httpRequest);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/account/update');

        return new Responder(
            $this->createMock(Environment::class),
            $urlGenerator,
            $requestStack,
        );
    }

    private function makeForm(bool $submitted, bool $valid): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('isSubmitted')->willReturn($submitted);
        $form->method('isValid')->willReturn($valid);
        $form->method('getErrors')->willReturn(new FormErrorIterator($form, []));

        return $form;
    }
}
