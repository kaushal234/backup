<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\CQRS\Query\User\FindUserQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\Country;
use App\DataTransferObject\User\UpdateUser;
use App\Form\Type\User\UserType;
use App\Http\Responder;
use App\Sdk\Resource\User as UserResource;
use App\Security\User\User;
use App\Service\CountryPhonePrefixResolver;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
#[Route(path: 'account')]
class FormAccountController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly Responder $responder,
        private readonly FormFactoryInterface $formFactory,
        private readonly CountryPhonePrefixResolver $phonePrefixResolver,
    ) {
    }

    #[Route(path: '/update', name: 'account:form', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(#[CurrentUser] User $currentUser): Response
    {
        /** @var UserResource $user */
        $user = $this->queryBus->dispatch(new FindUserQuery($currentUser->id));
        $country = null;

        if ($user->address->country) {
            $country = new Country();
            $country->iri = $user->address->country->iri;
        }

        $data = new UpdateUser();
        $data->iri = $user->iri;
        $data->profileIri = $user->profileIri;
        $data->state = $user->address->state;
        $data->city = $user->address->city;
        $data->country = $country;
        $data->postalCode = $user->address->postalCode;
        $data->street = $user->address->street;
        $data->street2 = $user->address->street2;
        $data->lastname = $user->lastname;
        $data->firstname = $user->firstname;
        $data->division = $user->division;
        $data->department = $user->department;
        $data->title = $user->title;
        $data->reception = $user->contact->reception;
        $data->mobile = $user->contact->mobile;
        $data->phone = $user->contact->phone;
        $data->fax = $user->contact->fax;
        $data->language = $user->language;

        $phonePrefix = $this->phonePrefixResolver->resolve($user->address?->country?->isoCode2);

        $form = $this->formFactory->create(UserType::class, $data, ['phone_prefix' => $phonePrefix]);

        return $this->responder->render('user/update_details.html.twig', ['form' => $form->createView()]);
    }
}
