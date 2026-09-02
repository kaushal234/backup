<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Responder;
use App\Sdk\Utils\IriToId;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
#[Route(path: '/switch-customer', )]
class SwitchCustomerController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route(path: '/{id}', name: 'switch_customer', methods: [Request::METHOD_GET])]
    public function __invoke(#[CurrentUser] User $user, ?int $id = null): Response
    {
        if (null === $id) {
            $this->responder->flash('danger', 'extranet.error.customer_id');

            return $this->responder->route('index');
        }

        $sessionSet = false;
        foreach ($user->acls as $acl) {
            if (IriToId::iriToId($acl->customerRelationshipTeam->customer->iri) !== $id || $sessionSet) {
                continue;
            }

            $this->requestStack->getSession()->set('customerRelationshipTeam', $acl->customerRelationshipTeam);
            $this->requestStack->getSession()->set('customer', $acl->customerRelationshipTeam->customer);

            $sessionSet = true;
        }

        return $this->responder->route('index');
    }
}
