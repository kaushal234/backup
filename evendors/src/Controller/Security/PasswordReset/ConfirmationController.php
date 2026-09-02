<?php

declare(strict_types=1);

namespace App\Controller\Security\PasswordReset;

use App\CQRS\Command\Security\PasswordReset\CheckPasswordResetTokenCommand;
use App\CQRS\Command\Security\PasswordReset\ConfirmPasswordResetCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\Security\PasswordReset\PasswordResetConfirmation;
use App\Form\Security\PasswordReset\PasswordResetConfirmationType;
use App\Form\ViolationMapper;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/security/reset-password-confirmation')]
final class ConfirmationController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly CommandBusInterface $bus,
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route('/{id}/{token}', name: 'security:password-reset-confirmation:request', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function request(int $id, string $token, Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_GET)) {
            try {
                $this->bus->dispatch(new CheckPasswordResetTokenCommand($id, $token));
            } catch (HandlerFailedException) {
                return $this->redirectWithError();
            }
        }

        $dto = new PasswordResetConfirmation();
        $form = $this->factory->create(PasswordResetConfirmationType::class, $dto, [
            'action' => $this->urlGenerator->generate('security:password-reset-confirmation:request', ['id' => $id, 'token' => $token]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(new ConfirmPasswordResetCommand($id, $token, $dto->newPassword));

                $this->responder->flash('success', 'security.password_reset_confirmation.success');

                return $this->responder->route('security:login');
            } catch (HandlerFailedException $e) {
                $nested = array_values($e->getWrappedExceptions(ClientException::class));

                if ($nested && 422 === $nested[0]->getCode()) {
                    $this->violationMapper->mapToForm($nested[0], $form, ['clearPassword' => 'newPassword.first']);
                } else {
                    return $this->redirectWithError();
                }
            }
        }

        return $this->responder->render('security/password-reset/confirmation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function redirectWithError(): RedirectResponse
    {
        $this->responder->flash('danger', 'security.password_reset_confirmation.error');

        return $this->responder->route('security:password-reset:index');
    }
}
