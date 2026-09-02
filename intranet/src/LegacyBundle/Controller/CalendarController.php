<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Controller\Mis\TroubleTicket\ShowController;
use AppBundle\Controller\Task\TaskController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * Migration HACK.
     *
     * URLs in module package CALENDAR
     * So we catch the legacy url and forward to our controller
     * only for migrated 'mode'.
     * Otherwise, throw NotFoundException to fallback on the legacy.
     */
    #[Route(path: '/calendar/calendar.php', name: 'legacy_calendar', defaults: ['alvest_module' => 'TASK'])]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];
        $id = $request->query->get('id');

        if (isset($m[0])) {
            switch ($m[0]) {
                case 'seq':
                    $request->attributes->set('alvest_module', 'SEQ');
                    break;
                case 'gwf':
                    $request->attributes->set('alvest_module', 'GWF');
                    break;
                case 'events':
                    return $this->redirectToRoute('event_home');
                case 'tasks':
                    if (isset($m[1]) && 'task' === $m[1]) {
                        if (isset($m[2]) && 'view' === $m[2]) {
                            try {
                                $troubleTicket = $this->client->findOneBy(ShowController::RESOURCE_URL, ['legacyId' => $id]);

                                return $this->redirectToRoute('trouble_ticket_show', ['id' => Iri::id($troubleTicket)]);
                            } catch (\RangeException $e) {
                                try {
                                    $task = $this->client->findOneBy(TaskController::RESOURCE_URL, ['legacyId' => $id]);

                                    return $this->redirectToRoute('task_show', ['id' => Iri::id($task)]);
                                } catch (\RangeException $e) {
                                    // do nothing, Task is not migrated
                                }
                            }
                        }
                    }
                    break;
            }
        }

        /*
         * SEQUENCE MODULE
         */

        if ($m === ['seq', 'new', 'hr.user.new']) {
            return $this->redirectToRoute('directory_people_add');
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/calendar/events/holidays_admin.php', name: 'legacy_event_admin', methods: 'GET|POST')]
    public function eventAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('event_home');
    }
}
