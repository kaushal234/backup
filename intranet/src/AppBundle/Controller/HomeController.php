<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Model\User;
use AppBundle\Form\Type\Directory\People\HomePeopleSearchChoiceType;
use AppBundle\Manager\Task\TaskManager;
use LegacyBundle\Form\LegacyModuleFormType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly TaskManager $taskManager,
    ) {
    }

    #[Route(path: '', name: 'home', methods: 'GET')]
    #[Template('home.html.twig')]
    public function home(): array
    {
        $moduleForm = $this->createForm(LegacyModuleFormType::class);
        $locations = $this->client->findBy(
            resource: 'locations',
            query: [
                'capability.factory' => true,
                'exists' => [
                    'contact.serviceHubTelephone' => true,
                ],
            ],
            options: [
                'cache' => true,
            ],
        );

        $psms = $this->client->findBy(resource: 'people', query: ['position.code' => 'PSM', 'disabled' => false, 'hidden' => false], options: ['cache' => true]);
        $pses = $this->client->findBy(resource: 'people', query: ['position.code' => 'PSE', 'disabled' => false, 'hidden' => false], options: ['cache' => true]);

        $peopleQuickSearchForm = $this->createForm(HomePeopleSearchChoiceType::class);

        return [
            'psms' => $psms,
            'pses' => $pses,
            'locations' => $locations,
            'moduleForm' => $moduleForm->createView(),
            'peopleQuickSearchForm' => $peopleQuickSearchForm->createView(),
        ];
    }

    /**
     * Displays the task dashboard grouped by module.
     */
    #[Route('/my-tasks', name: 'my_tasks')]
    #[Template('task/my_task.html.twig')]
    public function myTaskReport(): array
    {
        /** @var User $user */
        $user = $this->getUser();
        $userConnected = $user->getIriId();

        $migratedTasks = $this->client->findBy(\sprintf('base_tasks?%s', http_build_query(['assignee' => $user->getIriId(), 'status' => TaskManager::OPEN_TROUBLE_TICKET_STATUSES])));
        $derogations = $this->client->findBy(\sprintf('quality/derogations?%s', http_build_query(['assignee' => $userConnected, 'status' => 'OPEN'])));

        $allTasks = array_merge($migratedTasks->all(), $derogations->all());
        $report = $this->taskManager->buildMyTaskReport($allTasks, $userConnected);

        return [
            'globalTaskRows' => $report['globalTaskRows'],
            'otherRows' => $report['otherRows'],
            'columnsTotals' => $report['columnsTotals'],
            'footerUrls' => $report['footerUrls'],
        ];
    }
}
