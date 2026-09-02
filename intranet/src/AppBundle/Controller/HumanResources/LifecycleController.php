<?php

declare(strict_types=1);

namespace AppBundle\Controller\HumanResources;

use ApiBundle\Client;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Directory\PeopleLeaversListDataTableType;
use AppBundle\DataTable\Type\Directory\PeopleNewComersListDataTableType;
use AppBundle\Manager\Mis\ThirdPartyApp\MemberManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: 'human-resources', defaults: ['alvest_module' => 'ESM', 'moduleDomain' => 'human_resources_employee_staffing'])]
class LifecycleController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    private const DEFAULT_RANGE_DURATION = '-3 months';
    private const NORMALIZATION_GROUP_KEY = 'normalization_groups';
    private const NORMALIZATION_GROUP_VALUE = ['people_detail', 'update_task:read', 'module', 'application', 'premise', 'support_team'];

    #[Route(path: '/new-comers/report', name: 'human_resources_new_comers_home', methods: ['GET', 'POST'])]
    #[Template('human_resources/people_lifecycle/report.html.twig')]
    public function report(Request $request)
    {
        $parameters = http_build_query([
            'peopleCurrentlyOrFutureEnabled' => [
                'enableAt' => (new \DateTime(self::DEFAULT_RANGE_DURATION))->format('Y-m-d'),
            ],
            self::NORMALIZATION_GROUP_KEY => self::NORMALIZATION_GROUP_VALUE,
        ]);
        $datatable = $this->createDataTable(PeopleNewComersListDataTableType::class, PeopleNewComersListDataTableType::RESOURCE.'?'.$parameters);

        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'peopleListDatatable' => $datatable->createView(),
            'showAccessWarning' => !$this->isGranted('FEATURE_FILTER_PEOPLE_INCOMING'),
        ];
    }

    #[Route(path: '/leavers/report', name: 'human_resources_leavers_people', methods: ['GET', 'POST'])]
    #[Template('human_resources/people_lifecycle/report.html.twig')]
    public function recentAndComingLeavers(Request $request)
    {
        $parameters = http_build_query([
            'peopleCurrentlyOrFutureDisabled' => [
                'disabledAt' => (new \DateTime(self::DEFAULT_RANGE_DURATION))->format('Y-m-d'),
            ],
            self::NORMALIZATION_GROUP_KEY => self::NORMALIZATION_GROUP_VALUE,
        ]);

        $datatable = $this->createDataTable(PeopleLeaversListDataTableType::class, PeopleLeaversListDataTableType::RESOURCE.'?'.$parameters);

        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'peopleListDatatable' => $datatable->createView(),
            'showAccessWarning' => !$this->isGranted('FEATURE_FILTER_PEOPLE_LEAVING'),
        ];
    }

    #[Route(path: '/directory/third_party_apps/{updateTaskId}/grant_access_accepted_ajax/{moduleId}', name: 'directory_people_third_party_app_grant_access_accepted_ajax', methods: ['POST'])]
    public function grantAccessAcceptedAjax(int $updateTaskId, int $moduleId): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$moduleId);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        $updateTask = $this->container->get(Client::class)->find('modules/third_party_app/update_tasks', $updateTaskId);

        try {
            $this->container->get(MemberManager::class)->acceptGrantAccess($updateTask);

            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred while accepting grant access.'], 500);
        }
    }

    #[Route(path: '/directory/third_party_apps/{updateTaskId}/grant_access_denied_ajax/{moduleId}', name: 'directory_people_third_party_app_grant_access_denied_ajax', methods: ['POST'])]
    public function grantAccessDeniedAjax(int $updateTaskId, int $moduleId): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$moduleId);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        $client = $this->container->get(Client::class);
        $updateTask = $client->find('modules/third_party_app/update_tasks', $updateTaskId);

        try {
            $this->container->get(MemberManager::class)->denyGrantAccess($updateTask);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred while denying access grant.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $client->post('modules/blacklist/move', ['json' => ['user' => $updateTask->user, 'module' => $updateTask->thirdPartyApp]]);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Access grant denied, but moving user to blacklist failed.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'success']);
    }

    #[Route(path: '/directory/third_party_apps/{updateTaskId}/remove_access_accepted_ajax/{moduleId}', name: 'directory_people_third_party_app_remove_access_accepted_ajax', methods: ['POST'])]
    public function removeAccessAcceptedAjax(int $updateTaskId, int $moduleId): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$moduleId);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        $updateTask = $this->container->get(Client::class)->find('modules/third_party_app/update_tasks', $updateTaskId);

        try {
            $this->container->get(MemberManager::class)->acceptRemoveAccess($updateTask);

            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred while accepting remove access.'], 500);
        }
    }

    #[Route(path: '/directory/third_party_apps/{updateTaskId}/remove_access_denied_ajax/{moduleId}', name: 'directory_people_third_party_app_remove_access_denied_ajax', methods: ['POST'])]
    public function removeAccessDeniedAjax(int $updateTaskId, int $moduleId): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$moduleId);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        $client = $this->container->get(Client::class);
        $updateTask = $client->find('modules/third_party_app/update_tasks', $updateTaskId);

        try {
            $this->container->get(MemberManager::class)->denyRemoveAccess($updateTask);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred while access removal denied.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $client->post('modules/whitelist/move', ['json' => ['user' => $updateTask->user, 'module' => $updateTask->thirdPartyApp]]);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Access removal denied, but moving user to whitelist failed.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'success']);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            MemberManager::class,
        ]);
    }
}
