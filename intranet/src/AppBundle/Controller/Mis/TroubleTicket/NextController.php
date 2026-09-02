<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Manager\Task\TaskManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
readonly class NextController
{
    public function __construct(
        private TaskManager $taskManager,
    ) {
    }

    #[Route(path: '/{id}/next-trouble-ticket', name: 'next_trouble_ticket')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket, Request $request): RedirectResponse
    {
        return $this->taskManager->nextTask($request, $troubleTicket, true);
    }
}
