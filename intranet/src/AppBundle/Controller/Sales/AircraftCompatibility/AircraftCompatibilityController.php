<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\AircraftCompatibility;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Type\Sales\AircraftCompatibility\AircraftDataTableType;
use AppBundle\DataTable\Type\Sales\AircraftCompatibility\ProductDataTableType;
use AppBundle\Filters\Type\Sales\AircraftCompatibilityFilterType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/aircraft-compatibilities', defaults: ['alvest_module' => 'AC', 'breadcrumb_label' => 'menu.aircraft_compatibility.title', 'moduleDomain' => 'aircraft_compatibility'])]
class AircraftCompatibilityController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public const RESOURCE_URL = 'sales/aircraft_compatibilities';
    public const TRANSLATION_DOMAIN = 'nto';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            FileStreamedResponseFactory::class,
            FileManager::class,
        ]);
    }

    #[Route(path: '', name: 'aircraft_compatibility_home', methods: ['GET', 'POST'])]
    #[Template('sales/aircraft_compatibility/home.html.twig')]
    public function home(Request $request)
    {
        $client = $this->container->get(Client::class);

        $filterForm = $this->createForm(AircraftCompatibilityFilterType::class, [], [
            'action' => $this->generateUrl('aircraft_compatibility_home'),
            'method' => Request::METHOD_GET,
        ])->handleRequest($request);

        $data = [
            'rows' => [],
            'yTotals' => [],
            'xTotals' => [],
        ];
        $allProducts = [];
        $allAircrafts = [];
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $formData = $filterForm->getData();
            $aircraftCompatibilities = $client->findBy(self::RESOURCE_URL, $formData);

            foreach ($aircraftCompatibilities as $aircraftCompatibility) {
                foreach ($aircraftCompatibility['products'] as $product) {
                    if (
                        (0 !== \count($formData['products'] ?? []) && !\in_array($product['@id'], $formData['products'], true))
                        || (0 !== \count($formData['products.family'] ?? []) && !\in_array($product['family']['@id'], $formData['products.family'], true))
                        || (0 !== \count($formData['products.family.productType'] ?? []) && !\in_array($product['family']['productType']['@id'], $formData['products.family.productType'], true))
                    ) {
                        continue;
                    }
                    $productName = $product['name'];
                    $allProducts[$productName] = true;
                    foreach ($aircraftCompatibility['aircrafts'] as $aircraft) {
                        if (
                            (0 !== \count($formData['aircrafts'] ?? []) && !\in_array($aircraft['@id'], $formData['aircrafts'], true))
                            || (null !== $aircraft['manufacturer'] && 0 !== \count($formData['aircrafts.manufacturer'] ?? []) && !\in_array($aircraft['manufacturer']['@id'], $formData['aircrafts.manufacturer'], true))
                        ) {
                            continue;
                        }
                        $aircraftName = $aircraft['name'];
                        $allAircrafts[$aircraftName] = true;

                        $data['yTotals'][$productName] = 0;
                        $data['xTotals'][$aircraftName] = 0;

                        $data['rows'][$aircraftName][$productName] = [
                            'compatible' => true,
                            'id' => $aircraftCompatibility['id'],
                        ];
                    }
                }
            }

            foreach (array_keys($allAircrafts) as $aircraftName) {
                foreach (array_keys($allProducts) as $productName) {
                    if (!isset($data['rows'][$aircraftName][$productName])) {
                        $data['rows'][$aircraftName][$productName] = [
                            'compatible' => false,
                            'id' => null,
                        ];
                    }
                }
            }

            ksort($data['rows']);
            ksort($data['yTotals']);
            ksort($data['xTotals']);

            foreach ($data['rows'] as &$aircrafts) {
                ksort($aircrafts);
            }
        }

        return [
            'data' => $data,
            'filterForm' => $filterForm->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'aircraft_compatibility_show', methods: ['GET'])]
    #[Template('sales/aircraft_compatibility/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $aircraftCompatibility, Request $request)
    {
        $productDatatable = $this->createDataTable(ProductDataTableType::class, \sprintf('/sales/aircraft_compatibilities/%s/products', $aircraftCompatibility->getIriId()));
        $productDatatable->handleRequest($request);

        if ($productDatatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($productDatatable);
        }

        $aircraftDatatable = $this->createDataTable(AircraftDataTableType::class, \sprintf('/sales/aircraft_compatibilities/%s/aircrafts', $aircraftCompatibility->getIriId()));
        $aircraftDatatable->handleRequest($request);

        if ($aircraftDatatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($aircraftDatatable);
        }

        return [
            'aircraftCompatibility' => $aircraftCompatibility,
            'productDatatable' => $productDatatable->createView(),
            'aircraftDatatable' => $aircraftDatatable->createView(),
        ];
    }

    #[Route(path: '/{aircraftCompatibilityId}/file/{id}', name: 'aircraft_compatibility_file_show', methods: 'GET')]
    public function showFile(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'aircraftCompatibilityId'])] ApiData $aircraftCompatibility, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('sales/aircraft_compatibilities/%s/files/%s', $aircraftCompatibility['id'], $id));
    }

    #[Route(path: '/{aircraftCompatibilityId}/files/{id}/delete', name: 'aircraft_compatibility_file_delete', requirements: ['aircraftCompatibilityId' => '\d+', 'id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_EDIT_AIRCRAFT_COMPATIBILITY") or is_granted("MOO_AC")'))]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'aircraftCompatibilityId'])] ApiData $aircraftCompatibility, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_aircraft_compatibility_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('aircraft_compatibility_show', ['id' => $aircraftCompatibility['id']]);
        }

        $this->container->get(FileManager::class)->deleteFile($aircraftCompatibility, self::RESOURCE_URL, \sprintf('files/%s', $id));

        return $this->redirectToRoute('aircraft_compatibility_show', ['id' => $aircraftCompatibility['id']]);
    }

    #[Route(path: '/add', name: 'aircraft_compatibility_add', methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_CREATE_AIRCRAFT_COMPATIBILITY") or is_granted("MOO_AC")'))]
    #[Template('sales/aircraft_compatibility/form.html.twig')]
    public function add(): array
    {
        return [];
    }

    #[Route(path: '/{id}/edit', name: 'aircraft_compatibility_edit', methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_EDIT_AIRCRAFT_COMPATIBILITY") or is_granted("MOO_AC")'))]
    #[Template('sales/aircraft_compatibility/form.html.twig')]
    public function edit(): array
    {
        return [];
    }

    #[Route(path: '/{id}/add-file', name: 'aircraft_compatibility_add_file', methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_EDIT_AIRCRAFT_COMPATIBILITY") or is_granted("MOO_AC")'))]
    #[Template('sales/aircraft_compatibility/form.html.twig')]
    public function addFile(): array
    {
        return [];
    }

    #[Route(path: '/{id}/delete', name: 'aircraft_compatibility_delete', methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_DELETE_AIRCRAFT_COMPATIBILITY") or is_granted("MOO_AC")'))]
    public function delete(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $aircraftCompatibility): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_aircraft_compatibility', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('aircraft_compatibility.errors.csrf_delete', [], 'aircraft_compatibility'));

            return $this->redirectToRoute('aircraft_compatibility_show', ['id' => $aircraftCompatibility['id']]);
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $aircraftCompatibility['id']);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('aircraft_compatibility.success.delete', [], 'aircraft_compatibility'));
        } catch (ClientException $exception) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('aircraft_compatibility.errors.delete', [], 'aircraft_compatibility'));
        }

        return $this->redirectToRoute('aircraft_compatibility_home');
    }
}
