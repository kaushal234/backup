<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Service\MaintenanceContractsFilterType;
use AppBundle\Form\Type\Service\MaintenanceContractType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service/maintenance-contracts', defaults: ['breadcrumb_label' => 'menu.maintenance_contracts.title', 'moduleDomain' => 'service_maintenance_contracts'])]
class MaintenanceContractsController extends AbstractController
{
    private readonly Client $client;

    private readonly ViolationMapper $violationMapper;

    private readonly FormFactoryInterface $formFactory;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, ViolationMapper $violationMapper, FormFactoryInterface $formFactory, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->violationMapper = $violationMapper;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'service_maintenance_contracts_home')]
    #[Template('service/maintenance_contracts/list.html.twig')]
    public function list(Request $request)
    {
        $formFilter = $this->formFactory
            ->createNamed(
                '',
                MaintenanceContractsFilterType::class, [],
                [
                    'action' => $this->generateUrl('service_maintenance_contracts_home'),
                    'method' => 'GET',
                ]);

        $parameters = [];
        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = $formFilter->getData();
            $parameters['pagination'] = false;
        }

        try {
            $contracts = $this->client->findBy('maintenance_contracts', $parameters);
        } catch (ClientException $e) {
            $contracts = [];
        }

        return [
            'contracts' => $contracts,
            'formFilter' => $formFilter->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'service_maintenance_contracts_show', methods: 'GET')]
    #[Template('service/maintenance_contracts/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $maintenanceContract)
    {
        return ['contract' => $maintenanceContract];
    }

    #[Route(path: '/add', name: 'service_maintenance_contracts_add', methods: 'GET|POST')]
    #[Template('service/maintenance_contracts/add.html.twig')]
    #[IsGranted('FEATURE_MAINTENANCE_CONTRACT_WRITE')]
    public function add(Request $request)
    {
        $form = $this->createForm(MaintenanceContractType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->getClickedButton() && 'finish' === $form->getClickedButton()->getName()) {
            try {
                $contract = $this->client->save('maintenance_contracts', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('service.maintenance_contract.messages.success.add', [], 'service')
                );

                return $this->redirectToRoute('service_maintenance_contracts_show', ['id' => Iri::id($contract['@id'])]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'service_maintenance_contracts_edit', methods: 'GET|POST')]
    #[Template('service/maintenance_contracts/edit.html.twig')]
    #[IsGranted('FEATURE_MAINTENANCE_CONTRACT_WRITE')]
    public function edit(#[ApiValueResolverAttribute] ApiData $maintenanceContract, Request $request)
    {
        $form = $this->createForm(MaintenanceContractType::class, $maintenanceContract, [
            'mode' => 'edit',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $contract = $this->client->save('maintenance_contracts', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('service.maintenance_contract.messages.success.edit', ['%code%' => $contract['id']], 'service')
                );

                return $this->redirectToRoute('service_maintenance_contracts_show', ['id' => $contract['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'contract' => $maintenanceContract,
        ];
    }
}
