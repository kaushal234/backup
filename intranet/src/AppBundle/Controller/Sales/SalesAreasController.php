<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Sales\SalesArea\SalesAreaASMType;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales', defaults: ['alvest_module' => 'CTRY', 'breadcrumb_label' => 'menu.sales_areas.title', 'moduleDomain' => 'sales_areas'])]
class SalesAreasController extends AbstractController
{
    final public const RESOURCE_URL = 'sales_areas';

    private readonly Client $client;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    private readonly FormFactoryInterface $formFactory;

    public function __construct(Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory)
    {
        $this->client = $client;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
    }

    #[Route(path: '/sales-areas', name: 'sales_areas_home')]
    #[Template('sales/areas/list.html.twig')]
    public function index(
        #[ApiValueResolverAttribute(parameters: ['filters' => ['normalization_groups' => ['sales_area_public', 'people_public', 'network']]])] HydraCollection $countries,
        #[ApiValueResolverAttribute] HydraCollection $networks,
    ) {
        $asmsPerNetwork = array_fill_keys(array_column($networks->getSimpleArrayCopy(), 'name'), []);

        $countriesFormatted = $countries->getSimpleArrayCopy();
        foreach ($countriesFormatted as &$country) {
            $country['asms'] = $asmsPerNetwork;
            foreach ($country['salesAreas'] as $salesArea) {
                if (null === $salesArea['sso']['network']) {
                    continue;
                }
                $country['asms'][$salesArea['sso']['network']['name']][] = \sprintf('%s %s', $salesArea['asm']['firstname'], $salesArea['asm']['lastname']);
            }
            $country['asms'] = array_map(static function (array $network) {
                return implode(', ', $network);
            }, $country['asms']);
        }

        return [
            'countries' => $countriesFormatted,
            'networks' => $networks,
        ];
    }

    #[Route(path: '/sales-areas/{id}/show', name: 'sales_areas_show', methods: ['GET', 'POST'])]
    #[Template('sales/areas/show.html.twig')]
    public function show(
        #[ApiValueResolverAttribute] ApiData $country,
        #[ApiValueResolverAttribute] HydraCollection $networks,
        Request $request,
    ) {
        $salesAreas = $this->client->findBy('sales_areas', ['country' => $country->getIri()]);

        $forms = [];

        $refresh = false;

        $inflector = InflectorFactory::create()->build();
        foreach ($networks as $network) {
            /** @var array|string[]|ApiData $network */
            $form = $this->formFactory->createNamed(\sprintf('sales_area_asm_%s', $inflector->tableize((string) ($network['name'] ?? ''))), SalesAreaASMType::class, [], [
                'exclude' => array_reduce($salesAreas->getSimpleArrayCopy(), static function ($memo, $salesArea) use ($network) {
                    if ($salesArea['sso']['network']['@id'] === $network['@id']) {
                        $memo[] = $salesArea['asm'];
                    }

                    return $memo;
                }, []),
                'network' => $network->getIri(),
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $payload = [
                    'country' => $country['@id'],
                    'asm' => $data['asm'],
                    'sso' => $data['sso'],
                ];

                try {
                    $this->client->save(self::RESOURCE_URL, $payload);
                    $this->addFlash(
                        'success',
                        $this->translator->trans('sales.sales_areas.asm_success', ['%country%' => $country['name']], 'sales')
                    );
                } catch (ClientException $e) {
                    $this->violationMapper->mapToForm($e, $form);
                }

                $refresh = true;
            }

            $forms[$network['name']] = $form->createView();
        }

        if ($refresh) {
            return $this->redirectToRoute('sales_areas_show', ['id' => $country->getIriId()]);
        }

        return [
            'country' => $country,
            'salesAreas' => $salesAreas,
            'forms' => $forms,
        ];
    }

    #[Route(path: '/sales-areas/{salesAreaId}/delete', name: 'sales_areas_asm_delete')]
    public function removeASM(#[ApiValueResolverAttribute(parameters: ['id' => 'salesAreaId'])] ApiData $salesArea): RedirectResponse
    {
        if (!$this->authorizationChecker->isGranted('FEATURE_COUNTRY_ASM_WRITE')) {
            $this->addFlash(
                'error',
                $this->translator->trans('security.warning.access_denied', [], 'messages')
            );

            return $this->redirectToRoute('sales_areas_show', ['id' => Iri::id($salesArea['country'])]);
        }

        try {
            $this->client->remove(self::RESOURCE_URL, $salesArea->getIriId());

            $this->addFlash(
                'success',
                $this->translator->trans('sales.sales_areas.delete_asm_success', ['%country%' => $salesArea['country']['name']], 'sales')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('errors.something_went_wrong', [], 'messages')
            );
        }

        return $this->redirectToRoute('sales_areas_show', ['id' => Iri::id($salesArea['country'])]);
    }
}
