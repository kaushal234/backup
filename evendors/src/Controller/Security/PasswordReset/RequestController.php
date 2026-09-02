<?php

declare(strict_types=1);

namespace App\Controller\Security\PasswordReset;

use App\CQRS\Command\Security\PasswordReset\SendPasswordResetEmailCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\Security\PasswordReset\PasswordResetRequest;
use App\Form\Security\PasswordReset\PasswordResetRequestType;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/security/password-reset')]
final class RequestController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly CommandBusInterface $bus,
        private readonly FormFactoryInterface $factory,
    ) {
    }

    #[Route('/request', name: 'security:password-reset:request', methods: [Request::METHOD_POST])]
    public function request(Request $request): Response
    {
        $dto = new PasswordResetRequest();
        $form = $this->factory->create(PasswordResetRequestType::class, $dto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(new SendPasswordResetEmailCommand($dto->email));
            } catch (HandlerFailedException $e) {
                if (($nested = array_values($e->getWrappedExceptions(ClientException::class))) && Response::HTTP_UNAUTHORIZED === $nested[0]->getCode()) {
                    $this->responder->flash('danger', 'security.password_reset_confirmation.error_email');

                    return $this->responder->route('security:password-reset:index');
                }

                $this->responder->flash('danger', 'security.password_reset_confirmation.error');

                return $this->responder->route('security:password-reset:index');
            }

            $this->responder->flash('success', 'security.password_reset.success');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        return $this->responder->route('security:password-reset:index');
    }
}
