<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\CQRS\Command\User\UpdateUserCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\User\FindUserQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\User\UpdateUser;
use App\Form\Type\User\UserType;
use App\Http\Responder;
use App\Sdk\Resource\User as UserResource;
use App\Security\User\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
#[Route(path: 'account')]
class UpdateAccountController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly FormFactoryInterface $formFactory,
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/update-form', name: 'account:update', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(#[CurrentUser] User $currentUser, Request $request): Response
    {
        /** @var UserResource $apiUser */
        $apiUser = $this->queryBus->dispatch(new FindUserQuery($currentUser->id));

        $user = new UpdateUser();
        $user->iri = $apiUser->iri;
        $user->profileIri = $apiUser->profileIri;

        $form = $this->formFactory->create(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->dispatch(new UpdateUserCommand($user));
            } catch (HandlerFailedException $exception) {
                /** @var ClientException $clientException */
                $clientException = array_values($exception->getWrappedExceptions(ClientException::class))[0];

                $this->responder->flash('danger', $clientException->getMessage());

                return $this->responder->route('account:form');
            }

            $this->responder->flash('success', 'extranet.success.user');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        return $this->responder->route('account:form');
    }
}
