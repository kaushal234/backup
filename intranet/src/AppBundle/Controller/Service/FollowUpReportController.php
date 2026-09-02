<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Support\EquipmentSerialsController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/service/maintenance-follow-up-reports')]
class FollowUpReportController extends AbstractController
{
    private readonly Client $client;

    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    public function __construct(Client $client, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(path: '/by-equipment-record/{id}', requirements: ['id' => '\d+'], name: 'service_maintenance_follow_up_reports_list_by_equipment')]
    #[Template('service/maintenance_follow_up_reports/list.html.twig')]
    public function listByEquipmentRecord($id, Request $request)
    {
        $equipmentRecord = $this->client->find(EquipmentSerialsController::EQUIPMENT_RECORD_URL, $id);

        $query = ['equipmentRecord' => EquipmentSerialsController::EQUIPMENT_RECORD_URL."/$id"];
        $createdAt = [];
        if (null !== $start = $request->query->get('after')) {
            $createdAt['after'] = $start;
        }
        if (null !== $start = $request->query->get('before')) {
            $createdAt['before'] = $start;
        }
        if ($createdAt) {
            $query['createdAt'] = $createdAt;
        }
        try {
            $reports = $this->client->findBy('equipment_follow_up_reports', $query, ['hourmeterDate' => 'desc', 'createdAt' => 'desc']);
        } catch (ClientException $e) {
            $reports = [];
        }

        return [
            'equipmentRecord' => $equipmentRecord,
            'reports' => $reports,
        ];
    }

    #[Route(path: '/{id}/show', name: 'service_maintenance_follow_up_reports_show', methods: 'GET')]
    #[Template('service/maintenance_follow_up_reports/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $equipmentFollowUpReport)
    {
        return ['followUpReport' => $equipmentFollowUpReport];
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{parent}/files/{type}/{id}', name: 'service_maintenance_follow_up_reports_files_show', methods: 'GET', requirements: ['type' => 'accidents|maintenances', 'id' => '\d+'])]
    public function showFile($parent, $type, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('equipment_%s/%s/files/%s', $type, $parent, $id));
    }
}
