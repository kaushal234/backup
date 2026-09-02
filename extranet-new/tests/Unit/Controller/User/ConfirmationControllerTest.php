<?php

declare(strict_types=1);

namespace Unit\Controller\User;

use App\Controller\Security\PasswordReset\ConfirmationController;
use App\CQRS\Command\Security\PasswordReset\CheckPasswordResetTokenCommand;
use App\CQRS\Command\Security\PasswordReset\ConfirmPasswordResetCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\Security\PasswordReset\PasswordResetConfirmation;
use App\Form\ViolationMapper;
use App\Http\Responder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Twig\Environment;

final class ConfirmationControllerTest extends TestCase
{
    private CommandBusInterface&MockObject $bus;
    private FormFactoryInterface&MockObject $factory;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private ViolationMapper&MockObject $violationMapper;
    private ConfirmationController $controller;

    protected function setUp(): void
    {
        $twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->bus = $this->createMock(CommandBusInterface::class);
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->violationMapper = $this->createMock(ViolationMapper::class);

        $responder = new Responder(
            $twig,
            $this->urlGenerator,
            new RequestStack(),
        );

        $this->controller = new ConfirmationController(
            $responder,
            $this->bus,
            $this->factory,
            $this->urlGenerator,
            $this->violationMapper,
        );
    }

    // -------------------------------------------------------------------------
    // GET — Token check
    // -------------------------------------------------------------------------

    public function testGetWithValidTokenRendersForm(): void
    {
        $request = Request::create('/security/reset-password-confirmation/1/valid-token', 'GET');

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CheckPasswordResetTokenCommand::class));

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(false);
        $form->expects($this->once())->method('createView');

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('/security/reset-password-confirmation/1/valid-token');

        $response = $this->controller->request(1, 'valid-token', $request);

        // GET with valid token: form is rendered (not a redirect)
        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    public function testGetWithInvalidTokenRedirectsToPasswordResetIndex(): void
    {
        $request = Request::create('/security/reset-password-confirmation/1/invalid-token', 'GET');

        $envelope = new Envelope(new \stdClass());
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CheckPasswordResetTokenCommand::class))
            ->willThrowException(new HandlerFailedException($envelope, [new \RuntimeException('Invalid token')]));

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('security:password-reset:index')
            ->willReturn('/security/password-reset');

        $response = $this->controller->request(1, 'invalid-token', $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/security/password-reset', $response->getTargetUrl());
    }

    // -------------------------------------------------------------------------
    // POST — Password submission
    // -------------------------------------------------------------------------

    public function testPostWithValidPasswordRedirectsToLogin(): void
    {
        $request = Request::create('/security/reset-password-confirmation/1/valid-token', 'POST');

        $dto = new PasswordResetConfirmation();
        $dto->newPassword = 'Sup3rS3cur3P@ssword!';

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($dto);

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                ['security:password-reset-confirmation:request', ['id' => 1, 'token' => 'valid-token'], 0, '/security/reset-password-confirmation/1/valid-token'],
                ['security:login', [], 0, '/security/login'],
            ]);

        // Crucial: ConfirmPasswordResetCommand is dispatched on POST only
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ConfirmPasswordResetCommand::class));

        $response = $this->controller->request(1, 'valid-token', $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/security/login', $response->getTargetUrl());
    }

    public function testPostWithExpiredOrInvalidTokenRedirectsToPasswordResetIndex(): void
    {
        $request = Request::create('/security/reset-password-confirmation/1/expired-token', 'POST');

        $dto = new PasswordResetConfirmation();
        $dto->newPassword = 'Sup3rS3cur3P@ssword!';

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($dto);

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                ['security:password-reset-confirmation:request', ['id' => 1, 'token' => 'expired-token'], 0, '/security/reset-password-confirmation/1/expired-token'],
                ['security:password-reset:index', [], 0, '/security/password-reset'],
            ]);

        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('getStatusCode')->willReturn(410);
        $clientException = new ClientException($httpResponse);

        $envelope = new Envelope(new \stdClass());
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ConfirmPasswordResetCommand::class))
            ->willThrowException(new HandlerFailedException($envelope, [$clientException]));

        $response = $this->controller->request(1, 'expired-token', $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/security/password-reset', $response->getTargetUrl());
    }

    public function testPostWithWeakPasswordRendersFormWithErrors(): void
    {
        $request = Request::create('/security/reset-password-confirmation/1/valid-token', 'POST');

        $dto = new PasswordResetConfirmation();
        $dto->newPassword = 'weak';

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($dto);
        $form->expects($this->once())->method('createView');

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('/security/reset-password-confirmation/1/valid-token');

        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('getStatusCode')->willReturn(422);
        $clientException = new ClientException($httpResponse);

        $envelope = new Envelope(new \stdClass());
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ConfirmPasswordResetCommand::class))
            ->willThrowException(new HandlerFailedException($envelope, [$clientException]));

        // ViolationMapper must be called to map errors onto the form
        $this->violationMapper->expects($this->once())
            ->method('mapToForm')
            ->with($clientException, $form, ['clearPassword' => 'newPassword.first']);

        $response = $this->controller->request(1, 'valid-token', $request);

        // 422: form is re-rendered with errors, not a redirect
        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }

    public function testPostWithInvalidFormRendersForm(): void
    {
        $request = Request::create('/security/reset-password-confirmation/1/valid-token', 'POST');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $form->expects($this->once())->method('createView');

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('/security/reset-password-confirmation/1/valid-token');

        // No command dispatched when form is invalid
        $this->bus->expects($this->never())->method('dispatch');

        $response = $this->controller->request(1, 'valid-token', $request);

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }
}
