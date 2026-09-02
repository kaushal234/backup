<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Controller\Mis\Project\ProjectController;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MisController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/mis/mis.php', name: 'legacy_mis', defaults: ['alvest_module' => 'MIS'], methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        if ($request->request->has('m')) {
            throw $this->createNotFoundException();
        }

        $m = $request->query->all()['m'] ?? [];
        $id = $request->query->get('id');

        /*
         * GROUPS SECTION
         */
        if (isset($m[0])) {
            switch ($m[0]) {
                case 'grdesc':
                    return $this->redirectToRoute('mis_groups_home');
                case 'module':
                    if (null !== $id = $request->query->get('id')) {
                        try {
                            $module = $this->client->findOneBy('modules', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (isset($m[1], $m[2]) && [$m[1], $m[2]] === ['view', 'edit']) {
                            return $this->redirectToRoute('mis_modules_edit', ['id' => Iri::id($module)]);
                        }

                        return $this->redirectToRoute('mis_modules_show', ['id' => Iri::id($module)]);
                    }

                    return $this->redirectToRoute('mis_modules_home');
                case 'tts':
                    $request->attributes->set('alvest_module', 'TTS');
                    switch ($m[1] ?? null) {
                        case 'forms':
                            switch ($m[2]) {
                                case 'newticket':
                                    return $this->redirectToRoute('trouble_ticket_add');
                                case 'Comm':
                                    return $this->redirectToRoute('trouble_ticket_home');
                                case 'create':
                                    return $this->redirectToRoute('mis_project_add');
                                case 'byNumber':
                                    return $this->redirectToRoute('mis_project_home');
                            }
                            break;
                        case 'listing':
                            switch ($m[2]) {
                                case 'ticketQueues':
                                    return $this->redirectToRoute('trouble_ticket_home');
                                case 'timesheetsIT':
                                case 'timekeeping':
                                case 'internal':
                                case 'closed':
                                case 'mis':
                                    return $this->redirectToRoute('mis_project_home');
                            }
                            break;
                        case 'taskListing':
                            switch ($m[2]) {
                                case 'byDomainCategory':
                                case 'myOpenTickets':
                                case 'allOpenTickets':
                                case 'latest':
                                    return $this->redirectToRoute('trouble_ticket_home');
                            }
                            break;
                        case 'reports':
                            switch ($m[2] ?? []) {
                                case 'ttsKPI':
                                case 'closedTTS':
                                case 'statsByQueueDomain':
                                case 'taskStatsByYearByModule':
                                case 'TLDTaskDashboard':
                                case 'internal':
                                    return $this->redirectToRoute('trouble_ticket_home');
                            }
                            break;
                        case 'gantt':
                        case 'gantt2':
                        case 'map':
                            return $this->redirectToRoute('mis_project_home');
                        case 'view':
                            try {
                                $project = $this->client->findOneBy(ProjectController::RESOURCE_URL, ['legacyId' => $id]);
                            } catch (\RangeException $e) {
                                throw new LegacyResourceNotFoundException();
                            }

                            if (empty($m[2])) {
                                return $this->redirectToRoute('mis_project_show', ['id' => Iri::id($project)]);
                            }

                            switch ($m[2]) {
                                case 'reports':
                                case 'members':
                                case 'changeif':
                                case 'resc':
                                case 'changeStatus':
                                case 'delete':
                                case 'log':
                                case 'files':
                                case 'notes':
                                case 'conclusion':
                                case 'timesheets':
                                case 'tasks':
                                default:
                                    return $this->redirectToRoute('mis_project_show', ['id' => Iri::id($project)]);
                            }
                    }
                    break;
            }
        }

        throw $this->createNotFoundException();
    }
}
