<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Directory\PeopleFeatureListDataTableType;
use AppBundle\DataTable\Type\Mis\FeatureDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Acronym Controller.
 */
#[Route(path: '/mis/features', defaults: ['alvest_module' => 'MIS'])]
class FeatureController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    #[Route(path: '', name: 'mis_features_index', methods: 'GET')]
    #[Template('mis/features/list.html.twig')]
    #[IsGranted('FEATURE_GROUPS_ADMIN')]
    public function list(Request $request)
    {
        $datatable = $this->createDataTable(FeatureDataTableType::class, FeatureDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        return [
            'featureDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'mis_features_show', methods: 'GET')]
    #[Template('mis/features/show.html.twig')]
    #[IsGranted('FEATURE_GROUPS_ADMIN')]
    public function show(#[ApiValueResolverAttribute] ApiData $feature, Request $request)
    {
        $datatable = $this->createDataTable(
            PeopleFeatureListDataTableType::class,
            \sprintf(FeatureDataTableType::MEMBERS, $feature['@id']));
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return [
            'feature' => $feature,
            'featureDatatable' => $datatable->createView(),
        ];
    }
}
