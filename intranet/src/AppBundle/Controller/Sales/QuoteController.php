<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\QuoteDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/quotes', defaults: ['alvest_module' => 'SOR', 'breadcrumb_label' => 'menu.sales_areas.title', 'moduleDomain' => 'sales_areas'])]
class QuoteController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const RESOURCE_URL = 'sales/quotes';
    public const DELETE_TOKEN = 'delete_sales_quote_id';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '', name: 'sales_quotes_home', methods: ['GET', 'POST'])]
    #[Template('sales/quotes/home.html.twig')]
    public function index(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $quotes,
    ) {
        $datatable = $this->createDataTable(QuoteDataTableType::class, QuoteDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'quotes' => $quotes,
            'quoteDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_quotes_delete', methods: 'GET')]
    #[Template('sales/quotes/home.html.twig')]
    public function delete(Request $request, int $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('quotes.messages.errors.csrf', [], 'sales_orders'));

            return $this->redirectToRoute('sales_quotes_home');
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $id);

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('quotes.messages.success.delete', [], 'sales_orders'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('quotes.messages.errors.delete', [], 'directory'));
        }

        return $this->redirectToRoute('sales_quotes_home');
    }
}
