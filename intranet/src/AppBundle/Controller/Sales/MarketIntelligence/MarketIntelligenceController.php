<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\MarketIntelligence;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\MarketIntelligenceFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Sales\MarketIntelligence\MarketIntelligenceLinkType;
use AppBundle\Form\Type\SimpleSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/market-intelligences', defaults: ['alvest_module' => 'MIM', 'moduleDomain' => 'market_intelligence'])]
class MarketIntelligenceController extends AbstractController
{
    final public const RESOURCE_URL = 'sales/market_intelligences';

    final public const ALL_EMPLOYEES = 'ALL EMPLOYEES';
    final public const MANAGERS_AND_EXECUTIVES = 'MANAGERS AND EXECUTIVES ONLY';
    final public const EXECUTIVES = 'EXECUTIVES ONLY';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, ChartBuilderFactory::class, FileStreamedResponseFactory::class, FormFactoryInterface::class]);
    }

    #[Route(path: '', name: 'market_intelligence_home')]
    #[Template('sales/market_intelligence/home.html.twig')]
    public function home(Request $request)
    {
        $reportTitle = 'market_intelligence.report_title.latest';
        $searchTable = false;
        $pagination = false;

        $parameters = [
            'order' => [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'desc'],
            'itemsPerPage' => $request->query->get('itemsPerPage', 10),
        ];

        $formFactory = $this->container->get(FormFactoryInterface::class);
        $formFilter = $formFactory->createNamed('', MarketIntelligenceFilterType::class);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = array_merge($parameters, $formFilter->getData());
            $parameters['itemsPerPage'] = 20000;
            $parameters['order'] = [] === $request->query->all('order') ? $request->query->all('order') : ['id' => 'desc'];

            $reportTitle = 'market_intelligence.report_title.mim_filtered';
            $searchTable = true;
            $pagination = 20;
        }

        $simpleSearchForm = $this->createForm(SimpleSearchType::class, null, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'search_placeholder' => 'Search for...',
        ]);

        $simpleSearchForm->handleRequest($request);
        if ($simpleSearchForm->isSubmitted() && $simpleSearchForm->isValid()) {
            $reportTitle = 'market_intelligence.report_title.mim_filtered';
            $searchTable = true;
            $pagination = 20;
            $parameters = array_merge($parameters, $simpleSearchForm->getData());
            $parameters['itemsPerPage'] = 500;
        }

        $marketIntelligences = [];
        try {
            $marketIntelligences = $this->container->get(Client::class)->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->container->get(ViolationMapper::class)->mapToForm($e, $formFilter);
        }

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->container->get(Client::class)->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                return $this->redirectToRoute('market_intelligence_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Market Intelligence #%s does not exist', $id));
            }
        }

        $marketIntelligences = array_reduce($marketIntelligences->getSimpleArrayCopy(), static function ($memo, $marketIntelligence) {
            $marketIntelligence['marketIntelligencesLinked'] = array_merge($marketIntelligence['marketIntelligencesLinked'], $marketIntelligence['marketIntelligencesLinkedTo']);
            unset($marketIntelligence['marketIntelligencesLinkedTo']);
            $memo[] = $marketIntelligence;

            return $memo;
        }, []);

        $report = $this->container->get(Client::class)->get('reports/resource=/sales/market_intelligences;x=date;y=locations');

        $chartBuilder = $this->container->get(ChartBuilderFactory::class)
            ->getPieChartBuilder()
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('market_intelligence.chart.by_locations', [], 'market_intelligence'))
            ->addYAxis('Amount')
            ->disableLegend()
        ;

        foreach ($report['xTotals'] as $key => $value) {
            $chartBuilder->addPlot(
                'Value',
                $key,
                $value,
                [],
                ['name' => $key]
            );
        }

        return [
            'chart' => $chartBuilder->buildConfig(),
            'marketIntelligences' => $marketIntelligences,
            'formFilter' => $formFilter->createView(),
            'idSearchForm' => $idSearchForm->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'reportTitle' => $reportTitle,
            'searchTable' => $searchTable,
            'pagination' => $pagination,
            'search_form' => $simpleSearchForm->createView(),
        ];
    }

    #[Route(path: '/add', name: 'market_intelligence_add')]
    #[Route(path: '/{id}/edit', name: 'market_intelligence_edit')]
    #[Template('sales/market_intelligence/write.html.twig')]
    public function write(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $marketIntelligence = null)
    {
        if (null !== $marketIntelligence && !$this->isGranted('MARKET_INTELLIGENCE_ADMIN_VOTER', $marketIntelligence->getIri())) {
            throw new AccessDeniedException();
        }

        $productTypes = $this->container->get(Client::class)->findBy('sales/product_types', ['normalization_groups_override' => ['catalogue_type_list']], ['englishName' => 'asc'], ['raw_results' => true]);
        $marketIntelligenceTypes = $this->container->get(Client::class)->findBy('sales/market_intelligence_types', [], ['name' => 'ASC'], ['raw_results' => true]);
        $divisions = $this->container->get(Client::class)->findBy('divisions', [], ['name' => 'ASC'], ['raw_results' => true]);
        $filteredDivisions = array_filter(
            $divisions['hydra:member'],
            static function ($division) {
                return '/divisions/5' !== $division['@id'];
            }
        );
        $filteredDivisions = array_values($filteredDivisions);
        $positionLevels = $this->container->get(Client::class)->findBy('position_levels', [], ['label' => 'ASC']);

        $formattedPositionLevels = [
            self::ALL_EMPLOYEES => '',
            self::MANAGERS_AND_EXECUTIVES => [],
            self::EXECUTIVES => '',
        ];
        foreach ($positionLevels as $positionLevel) {
            if (\in_array($positionLevel['label'], ['OTHER EMPLOYEES WITHOUT DIRECT REPORT', 'SUPERVISOR'], true)) {
                continue;
            }

            if (\in_array($positionLevel['label'], ['MANAGERS', 'EXECUTIVES'], true)) {
                $formattedPositionLevels[self::MANAGERS_AND_EXECUTIVES][] = $positionLevel['@id'];
            }

            if ('EXECUTIVES' === $positionLevel['label']) {
                $formattedPositionLevels[self::EXECUTIVES] = ltrim(\sprintf('%s %s', $formattedPositionLevels[self::EXECUTIVES], $positionLevel['@id']));
            }

            if ('ALVEST STEERING COMMITTEE' === $positionLevel['label']) {
                $formattedPositionLevels[self::MANAGERS_AND_EXECUTIVES][] = $positionLevel['@id'];
                $formattedPositionLevels[self::EXECUTIVES] = ltrim(\sprintf('%s %s', $formattedPositionLevels[self::EXECUTIVES], $positionLevel['@id']));
            }
        }
        sort($formattedPositionLevels[self::MANAGERS_AND_EXECUTIVES]);
        $formattedPositionLevels[self::MANAGERS_AND_EXECUTIVES] = implode(' ', $formattedPositionLevels[self::MANAGERS_AND_EXECUTIVES]);

        return [
            'props' => [
                'formType' => null !== $marketIntelligence ? 'edition' : 'add',
                'divisions' => $filteredDivisions,
                'positionLevels' => $formattedPositionLevels,
            ],
            'initialState' => [
                'productType' => [
                    'productTypes' => $productTypes['hydra:member'],
                ],
                'marketIntelligence' => [
                    'details' => $marketIntelligence?->toArray(),
                    'marketIntelligenceTypes' => $marketIntelligenceTypes['hydra:member'],
                ],
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'market_intelligence_show', methods: 'GET|POST')]
    #[Template('sales/market_intelligence/show.html.twig')]
    #[IsGranted(attribute: 'MARKET_INTELLIGENCE_VIEW_VOTER', subject: new Expression('args["marketIntelligence"].getIri()'))]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $marketIntelligence, Request $request)
    {
        $marketIntelligence['marketIntelligencesLinked'] = array_merge($marketIntelligence['marketIntelligencesLinked'], $marketIntelligence['marketIntelligencesLinkedTo']);
        unset($marketIntelligence['marketIntelligencesLinkedTo']);

        $formLinkMIM = $this->createForm(MarketIntelligenceLinkType::class);

        $formLinkMIM->handleRequest($request);
        if ($formLinkMIM->isSubmitted() && $formLinkMIM->isValid()) {
            try {
                $data = $formLinkMIM->getData();
                $payload = [
                    '@id' => $marketIntelligence['@id'],
                    'marketIntelligencesLinked' => [$data['marketIntelligence']],
                ];

                $this->container->get(Client::class)->save(self::RESOURCE_URL, $payload);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('market_intelligence.link.success', [], 'market_intelligence')
                );

                return $this->redirectToRoute('market_intelligence_show', ['id' => Iri::id($marketIntelligence)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formLinkMIM);
            }
        }

        return [
            'marketIntelligence' => $marketIntelligence,
            'form_link' => $formLinkMIM->createView(),
        ];
    }

    #[Route(path: '/{id}/files', name: 'market_intelligence_files', methods: 'GET', defaults: ['label' => 'menu.files', 'domain' => 'messages'])]
    #[Template('sales/market_intelligence/files.html.twig')]
    #[IsGranted(attribute: 'MARKET_INTELLIGENCE_VIEW_VOTER', subject: new Expression('args["marketIntelligence"].getIri()'))]
    public function files(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $marketIntelligence)
    {
        return ['marketIntelligence' => $marketIntelligence];
    }

    #[Route(path: '/{id}/files_ajax', name: 'market_intelligence_files_ajax', methods: 'GET')]
    #[Template('sales/market_intelligence/files_ajax.html.twig')]
    public function marketIntelligenceFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $marketIntelligence)
    {
        return ['marketIntelligence' => $marketIntelligence];
    }

    #[Route(path: '/{marketIntelligenceId}/files/{id}', name: 'market_intelligence_files_show', methods: 'GET')]
    public function showFile($marketIntelligenceId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('sales/market_intelligences/%s/files/%s', $marketIntelligenceId, $id));
    }

    #[Route(path: '/{marketIntelligenceId}/files/{id}/delete', name: 'delete_market_intelligence_file', methods: ['GET'])]
    public function deleteFile(Request $request, $marketIntelligenceId, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_market_intelligence_file', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete file: please refresh your form.');

            return $this->redirectToRoute('market_intelligence_files', ['id' => $marketIntelligenceId]);
        }
        $operation = \sprintf('files/%s', $id);
        try {
            $this->container->get(Client::class)->request(self::RESOURCE_URL, $marketIntelligenceId, $operation, Request::METHOD_DELETE);
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.error.delete_file', [], 'first_article_qualification')
            );
        }

        return $this->redirectToRoute('market_intelligence_files', ['id' => $marketIntelligenceId]);
    }

    #[Route(path: '/{id}/delete', name: 'market_intelligence_delete', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: 'MARKET_INTELLIGENCE_ADMIN_VOTER', subject: new Expression('args["marketIntelligence"].getIri()'))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $marketIntelligence): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $marketIntelligence['id']);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('market_intelligence.delete.success', [], 'market_intelligence')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('market_intelligence.delete.error', [], 'market_intelligence')
            );
        }

        return $this->redirectToRoute('market_intelligence_home');
    }
}
