<?php

declare(strict_types=1);

namespace AppBundle\Controller\Legal;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Legal\CategoryDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/legal/contracts/categories')]
class CategoryController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const string RESOURCE_URL = 'contract/categories';
    public const string DELETE_TOKEN = 'delete_category_id';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(path: '', name: 'category_home', methods: ['GET'])]
    #[Template('/legal/category/home.html.twig')]
    public function list(Request $request)
    {
        $datatable = $this->createDataTable(CategoryDataTableType::class, CategoryDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'categoryDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'category_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Template('/legal/category/edit.html.twig')]
    public function edit(int $id)
    {
        return ['id' => $id];
    }
}
