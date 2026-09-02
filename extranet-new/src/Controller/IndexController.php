<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\EquipmentRecordSearchType;
use App\Http\Responder;
use App\Sdk\Resource\EquipmentRecord;
use App\Security\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[Route(path: '', name: 'index')]
    public function __invoke(Request $request): Response
    {
        if (!$this->security->isFullyAuthenticated()) {
            return $this->responder->route('security:login');
        }

        $searchForm = $this->formFactory->create(EquipmentRecordSearchType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            try {
                /** @var EquipmentRecord $equipment */
                $equipment = $searchForm->get('equipmentRecord')->getData();

                return $this->responder->route('equipment:show', ['id' => $equipment->id]);
            } catch (\Throwable) {
                $this->responder->flash('danger', 'ER not found');

                return $this->responder->redirect('index');
            }
        }

        return $this->responder->render('index.html.twig', [
            'customerRelationshipTeam' => $this->requestStack->getSession()->get('customerRelationshipTeam'),
            'form' => $searchForm->createView(),
        ]);
    }
}
