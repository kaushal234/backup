<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\Http\Responder;
use App\Security\Voter\TechnicianOnCallVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[Route(path: '/technician-on-calls')]
readonly class CreateController
{
    public function __construct(
        private Responder $responder,
    ) {
    }

    #[IsGranted(TechnicianOnCallVoter::CREATE)]
    #[Route(path: '/create', name: 'technician_on_call:create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        return $this->responder->render('technician_on_call/create.html.twig', [
            'equipmentId' => $request->query->get('equipmentRecordId'),
        ]);
    }
}
