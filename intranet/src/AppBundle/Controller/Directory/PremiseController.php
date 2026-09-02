<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Directory\PremiseListDataTableType;
use AppBundle\Filters\Type\Directory\PremiseTagFilterType;
use AppBundle\Form\Type\Directory\Premise\PremiseTransferType;
use AppBundle\Form\Type\Directory\Premise\PremiseType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(
    path: '/directory/premises',
    defaults: ['alvest_module' => 'PRE', 'breadcrumb_label' => 'Premise', 'moduleDomain' => 'directory_premise']
)]
class PremiseController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    final public const RESOURCE_URL = 'premises';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FormFactoryInterface::class]);
    }

    #[Route(path: '/dashboard', name: 'directory_premise_home', methods: ['GET'])]
    #[Template('directory/premise/dashboard.html.twig')]
    public function home(Request $request)
    {
        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('premiseFilterTag', PremiseTagFilterType::class);
        $formFilter->handleRequest($request);

        $query = [];
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $query = ['options' => ['type' => Iri::id($formFilter->getData()['tag'])]];
        }

        $reportPeopleByPremiseByBusinessUnit = $this->container->get(Client::class)->get('/reports/resource=/people;x=premise.name;y=businessUnit.name', ['query' => $query]);

        return [
            'premiseFilterTag' => $formFilter->createView(),
            'reportData' => $reportPeopleByPremiseByBusinessUnit,
        ];
    }

    #[Route(path: '/list', name: 'directory_premise_list', methods: ['GET', 'POST'])]
    #[Template('directory/premise/home.html.twig')]
    public function list(Request $request)
    {
        $datatable = $this->createDataTable(PremiseListDataTableType::class, PremiseListDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'premiseListDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'directory_premise_show', methods: ['GET'])]
    #[Template('directory/premise/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $premise)
    {
        $people = $this->container->get(Client::class)->findBy('people', ['premise' => $premise['@id'], 'disabled' => false, 'hidden' => false]);
        $businessUnits = [];
        foreach ($people->getSimpleArrayCopy() as $user) {
            $businessUnits[$user['businessUnit']['name']][] = $user;
        }

        return [
            'premise' => $premise,
            'businessUnits' => $businessUnits,
        ];
    }

    #[Route(path: '/add', name: 'directory_premise_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'directory_premise_edit', methods: ['GET|POST'])]
    #[Template('directory/premise/write.html.twig')]
    #[IsGranted('FEATURE_PREMISE_WRITE')]
    public function write(Request $request, #[ApiValueResolverAttribute] ?ApiData $premise = null)
    {
        $form = $this->container->get(FormFactoryInterface::class)
            ->createNamed(
                'premise_form',
                PremiseType::class,
                $premise
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('directory.premise.edit.success', [], 'directory'));
                if (null === $premise) {
                    return $this->redirectToRoute('directory_premise_list');
                }

                return $this->redirectToRoute('directory_premise_show', ['id' => $premise['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'premise' => $premise,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/transfer', requirements: ['id' => '\d+'], name: 'directory_premises_transfer', methods: ['GET|POST'])]
    #[Template('directory/premise/transfer.html.twig')]
    #[IsGranted('FEATURE_PREMISE_WRITE')]
    public function transfer(Request $request, $id)
    {
        $client = $this->container->get(Client::class);
        $premise = $client->find(self::RESOURCE_URL, $id);
        $premiseForm = $this->container->get(FormFactoryInterface::class)->createNamed('premise_form', PremiseTransferType::class);

        $premiseForm->handleRequest($request);
        if ($premiseForm->isSubmitted() && $premiseForm->isValid()) {
            try {
                $targetPremise = $premiseForm->getData();

                $client->request(self::RESOURCE_URL, $premise->getIriId(), 'transfer', 'PUT',
                    [
                        'json' => [
                            'target' => $targetPremise['target'],
                        ],
                    ]);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('directory.premise.transfer.success', [], 'directory')
                );

                return $this->redirectToRoute('directory_premise_list');
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    $e->getMessage()
                );
                $this->container->get(ViolationMapper::class)->mapToForm($e, $premiseForm);
            }
        }

        return [
            'premise' => $premise,
            'form' => $premiseForm->createView(),
        ];
    }
}
