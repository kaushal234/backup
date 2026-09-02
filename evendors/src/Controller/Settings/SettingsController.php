<?php

declare(strict_types=1);

namespace App\Controller\Settings;

use App\CQRS\Command\Settings\ChangePasswordCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\Settings\ChangePassword;
use App\Form\Settings\ChangePasswordType;
use App\Form\ViolationMapper;
use App\Http\Responder;
use App\Security\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

final class SettingsController
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly Security $security,
        private readonly Responder $responder,
        private readonly CommandBusInterface $bus,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route('/settings', name: 'settings', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $user = $this->security->getAuthenticatedUser();

        $passwordForm = $this->factory->create(ChangePasswordType::class, new ChangePassword());
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            try {
                $this->bus->dispatch(new ChangePasswordCommand($passwordForm->getData()->password));
                $this->responder->flash('success', 'settings.change_password.success');

                return $this->responder->route('settings');
            } catch (HandlerFailedException $exception) {
                /** @var ClientException $clientException */
                $clientException = array_values($exception->getWrappedExceptions(ClientException::class))[0];
                $this->violationMapper->mapToForm(
                    $clientException,
                    $passwordForm,
                    ['clearPassword' => 'password']
                );
            }
        }

        if ($passwordForm->getErrors(true)->count() > 0) {
            $this->responder->flash('danger', 'settings.change_password.error');
        }

        return $this->responder->render('settings/index.html.twig', [
            'user' => $user,
            'passwordForm' => $passwordForm->createView(),
        ]);
    }
}
