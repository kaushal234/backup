<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\CQRS\Query\User\FindUserQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\User as UserResource;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
#[Route(path: 'account')]
class AccountController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly Responder $responder,
    ) {
    }

    #[Route(path: '', name: 'account:index', methods: [Request::METHOD_GET])]
    public function __invoke(#[CurrentUser] User $currentUser): Response
    {
        /** @var UserResource $user */
        $user = $this->queryBus->dispatch(new FindUserQuery($currentUser->id));

        return $this->responder->render('user/account.html.twig', ['user' => $user]);
    }
}
