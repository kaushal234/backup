<?php

declare(strict_types=1);

namespace AppBundle\Controller\Communication;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Communication\CampaignDataTableType;
use AppBundle\DataTable\Type\Communication\ContactCampaignDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/contact-campaigns', defaults: ['alvest_module' => 'Communication', 'moduleDomain' => 'contact_campaign'])]
class CampaignController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route('/', name: 'contact_campaign_home', methods: ['GET', 'POST'])]
    #[Template('communication/contact-campaign/home.html.twig')]
    public function home(Request $request): array|Response
    {
        $datatable = $this->createDataTable(CampaignDataTableType::class, 'contact_campaigns');
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return [
            'campaignDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'contact_campaign_show', methods: ['GET', 'POST'])]
    #[Template('communication/contact-campaign/show.html.twig')]
    public function show(Request $request, #[ApiValueResolverAttribute] ApiData $contactCampaign): array|Response
    {
        $datatable = $this->createDataTable(
            ContactCampaignDataTableType::class, \sprintf('contact_campaign/%d/contacts', $contactCampaign['id']),
            ['campaign_id' => $contactCampaign['id']]
        );
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return [
            'contactDatatable' => $datatable->createView(),
            'contactCampaign' => $contactCampaign,
        ];
    }

    #[Route(path: '/add', name: 'contact_campaign_add', methods: ['GET'])]
    #[Template('communication/contact-campaign/add.html.twig')]
    public function add(): array
    {
        return [];
    }

    #[Route(path: '/{id}/edit', name: 'contact_campaign_edit', methods: ['GET'])]
    #[Template('communication/contact-campaign/edit.html.twig')]
    public function edit(#[ApiValueResolverAttribute] ApiData $contactCampaign): array
    {
        return [
            'contactCampaign' => $contactCampaign,
        ];
    }

    #[Route(path: '/batch_tasks', name: 'contact_campaign_batch_task', methods: ['GET', 'POST'])]
    public function batchTaskCreation(Request $request): RedirectResponse
    {
        $module = $this->client->findOneBy('/modules', ['name' => 'XU']);
        $data = [
            'type' => 'contact.validation.address',
            'module' => $module->getIri(),
            'referenceId' => array_map(static fn (string $id) => (int) $id, $request->query->all('id')),
            'campaign' => \sprintf('/contact_campaigns/%d', $request->query->get('campaign_id')),
        ];

        try {
            $this->client->post('tasks/batch', ['json' => $data]);
            $this->addFlash('success', 'Tasks created successfully.');
        } catch (\Error $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('contact_campaign_show', ['id' => $request->query->get('campaign_id')]);
    }
}
