<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\CQRS\Command\User\SendPasswordEmailCommand;
use App\CQRS\Command\User\UpdatePasswordCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\User\UpdatePassword;
use App\Form\Type\User\UpdatePasswordType;
use App\Form\ViolationMapper;
use App\Http\Responder;
use App\Security\User\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
#[Route(path: 'account/update-password')]
class UpdatePasswordController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly CommandBusInterface $commandBus,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '', name: 'account:update_password', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(#[CurrentUser] User $user, #[MapQueryParameter] ?string $updateToken, Request $request): Response
    {
        if (null === $updateToken) {
            $this->commandBus->dispatch(new SendPasswordEmailCommand());
            $this->responder->flash('success', 'extranet.success.password_email');

            return $this->responder->route('account:index');
        }

        $passwordForm = $this->formFactory->create(UpdatePasswordType::class, new UpdatePassword());
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            try {
                $this->commandBus->dispatch(new UpdatePasswordCommand(password: $passwordForm->getData()->password, token: $updateToken));
                $this->responder->flash('success', 'settings.change_password.success');

                return $this->responder->route('account:index');
            } catch (HandlerFailedException $exception) {
                /** @var ClientException $clientException */
                $clientException = array_values($exception->getWrappedExceptions(ClientException::class))[0];
                if (Response::HTTP_BAD_REQUEST === $clientException->getCode()) {
                    $this->responder->flash('danger', 'extranet.error.password_token');
                } else {
                    $this->violationMapper->mapToForm(
                        $clientException,
                        $passwordForm,
                        ['clearPassword' => 'password']
                    );
                }
            }
        }

        if ($passwordForm->getErrors(true)->count() > 0) {
            $this->responder->flash('danger', 'settings.change_password.error');
        }

        return $this->responder->render('user/update_password.html.twig', ['form' => $passwordForm->createView()]);
    }
}
