<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\FreightForwarderFilterType;
use AppBundle\Form\Type\Sales\FreightForwarder\FreightForwarderType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/freight-forwarders', defaults: ['alvest_module' => 'SQR'])]
class FreightForwarderController extends AbstractController
{
    final public const RESOURCE_URL = 'freight_forwarders';

    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'freight_forwarders_home', methods: 'GET')]
    #[Template('sales/freight_forwarders/home.html.twig')]
    public function home(Request $request)
    {
        $parameters = ['itemsPerPage' => 10];
        $reportTitle = 'freight_forwarder.last_ones';
        $searchTable = false;

        $formFilter = $this->formFactory->createNamed('freight_forwarders_filter', FreightForwarderFilterType::class, null);
        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = array_merge(['itemsPerPage' => 2000, 'pagination' => false], $formFilter->getData());
            $reportTitle = 'freight_forwarder.filtered';
            $searchTable = true;
        }

        $freightForwarders = $this->client->findBy(self::RESOURCE_URL, $parameters, ['id' => 'desc']);

        return [
            'freightForwarders' => $freightForwarders,
            'formFilter' => $formFilter->createView(),
            'reportTitle' => $reportTitle,
            'searchTable' => $searchTable,
        ];
    }

    #[Route(path: '/{id}/show', name: 'freight_forwarders_show', methods: 'GET')]
    #[Template('sales/freight_forwarders/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $freightForwarder)
    {
        return ['freightForwarder' => $freightForwarder];
    }

    #[Route(path: '/add', name: 'freight_forwarders_add', methods: ['GET|POST'])]
    #[Template('sales/freight_forwarders/add.html.twig')]
    #[IsGranted('FEATURE_FREIGHT_FORWARDER_ADMIN')]
    public function add(Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'freight_forwarder_form',
                FreightForwarderType::class,
                [],
                ['add' => true]
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $freightForwarder = $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('freight_forwarder.add.success', [], 'freight_forwarder')
                );

                return $this->redirectToRoute('freight_forwarders_show', ['id' => $freightForwarder['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'freight_forwarders_edit', methods: 'GET|POST')]
    #[Template('sales/freight_forwarders/edit.html.twig')]
    #[IsGranted('FEATURE_FREIGHT_FORWARDER_ADMIN')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $freightForwarder)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'freight_forwarder_form',
                FreightForwarderType::class,
                $freightForwarder,
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('freight_forwarder.edit.success', [], 'freight_forwarder')
                );

                return $this->redirectToRoute('freight_forwarders_show', ['id' => Iri::id($freightForwarder)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'freightForwarder' => $freightForwarder,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'freight_forwarders_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_FREIGHT_FORWARDER_ADMIN')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $freightForwarder): RedirectResponse
    {
        try {
            $this->client->remove(self::RESOURCE_URL, $freightForwarder->getIriId());

            $this->addFlash(
                'success',
                $this->translator->trans('freight_forwarder.delete.success', [], 'freight_forwarder')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('freight_forwarder.delete.error', [], 'freight_forwarder')
            );
        }

        return $this->redirectToRoute('freight_forwarders_home');
    }
}
