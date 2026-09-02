<?php

declare(strict_types=1);

namespace App\Controller\Contact;

use App\CQRS\Command\Contact\ContactEmailCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\Contact\ContactEmail;
use App\Form\Type\Contact\ContactType;
use App\Http\Responder;
use App\Sdk\Resource\CustomerRelationshipTeam;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/contact-us')]
class EmailController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly CommandBusInterface $commandBus,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[Route(path: '/email', name: 'contact:email', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(ContactType::class, $contactForm = new ContactEmail())->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CustomerRelationshipTeam $customerRelationshipTeam */
            $customerRelationshipTeam = $request->getSession()->get('customerRelationshipTeam');

            $receiver = match ($contactForm->department) {
                ContactEmail::SERVICE => $customerRelationshipTeam->serviceLocation->contact->email,
                ContactEmail::SPARE_PARTS => $customerRelationshipTeam->partsLocation->contact->email,
                default => $customerRelationshipTeam->salesRepresentative->email,
            };

            $this->commandBus->dispatch(new ContactEmailCommand(
                message: $contactForm->message,
                receiver: $receiver,
            ));

            $this->responder->flash('success', 'extranet.success.contact');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        return $this->responder->route('contact:index');
    }
}
