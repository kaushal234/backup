<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\ChangeLog;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Mis\ChangeLog\ChangeLogDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Module Controller.
 */
#[Route(path: '/mis/change-logs', defaults: ['alvest_module' => 'MIS'])]
class ChangeLogController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;
    final public const CHANGE_LOG_URL = 'change_logs';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, FileStreamedResponseFactory::class, FormFactoryInterface::class]);
    }

    #[Route(path: '', name: 'mis_change_logs_index', methods: 'GET|POST')]
    #[Template('mis/change_logs/list.html.twig')]
    public function index(Request $request)
    {
        $datatable = $this->createDataTable(ChangeLogDataTableType::class, ChangeLogDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return [
            'datatable' => $datatable->createView(),
        ];
    }
}
