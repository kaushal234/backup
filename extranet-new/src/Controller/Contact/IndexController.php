<?php

declare(strict_types=1);

namespace App\Controller\Contact;

use App\DataTransferObject\Contact\ContactEmail;
use App\Form\Type\Contact\ContactType;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/contact-us')]
class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route(path: '', name: 'contact:index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(ContactType::class, new ContactEmail());

        return $this->responder->render('contact/index.html.twig', [
            'form' => $form->createView(),
            'customerRelationshipTeam' => $this->requestStack->getSession()->get('customerRelationshipTeam'),
        ]);
    }
}
