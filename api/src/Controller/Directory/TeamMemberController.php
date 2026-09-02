<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use App\Manager\Directory\TeamMemberManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeamMemberController extends AbstractController
{
    public function __construct(
        private readonly TeamMemberManager $teamMemberManager,
    ) {
    }

    #[Route(path: '/people/team_members', methods: ['GET'])]
    public function __invoke(Request $request)
    {
        $peoples = $this->teamMemberManager->get($request->query->all());
        $peopleIris = $this->teamMemberManager->transformToIris($peoples);

        return new JsonResponse($peopleIris);
    }
}
