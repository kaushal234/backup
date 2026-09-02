<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\AircraftCompatibility;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Type\Sales\AircraftCompatibility\AircraftDataTableType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/aircrafts', defaults: ['alvest_module' => 'AC', 'breadcrumb_label' => 'menu.aircraft.title', 'moduleDomain' => 'aircraft_compatibility'])]
class AircraftController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public const RESOURCE_URL = 'sales/aircrafts';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            FileStreamedResponseFactory::class,
            FileManager::class,
        ]);
    }

    #[Route(path: '', name: 'aircraft_home', methods: ['GET', 'POST'])]
    #[Template('sales/aircraft/home.html.twig')]
    public function home(Request $request)
    {
        $aircraftDatatable = $this->createDataTable(AircraftDataTableType::class, self::RESOURCE_URL, ['displayButtons' => true]);
        $aircraftDatatable->handleRequest($request);

        if ($aircraftDatatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($aircraftDatatable);
        }

        return [
            'aircraftDatatable' => $aircraftDatatable->createView(),
        ];
    }

    #[Route(path: '/add', name: 'aircraft_add', methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_CREATE_AIRCRAFT") or is_granted("MOO_AC")'))]
    #[Template('sales/aircraft/form.html.twig')]
    public function add(): array
    {
        return [];
    }

    #[Route(path: '/{id}/edit', name: 'aircraft_edit', methods: ['GET'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_EDIT_AIRCRAFT") or is_granted("MOO_AC")'))]
    #[Template('sales/aircraft/form.html.twig')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $aircraft): array
    {
        return [];
    }

    #[Route(path: '{id}/delete_confirmation_modal', name: 'aircraft_delete_confirm', methods: 'GET')]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_DELETE_AIRCRAFT") or is_granted("MOO_AC")'))]
    public function deleteConfirmation(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $aircraft): Response
    {
        return $this->render('sales/aircraft/modal/remove_confirmation.html.twig', [
            'aircraft' => $aircraft,
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'aircraft_delete', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression('is_granted("FEATURE_DELETE_AIRCRAFT") or is_granted("MOO_AC")'))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $aircraft, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_aircraft', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('aircraft_compatibility.delete.csrf_error', [], 'aircraft_compatibility'));

            return $this->redirectToRoute('aircraft_home');
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $aircraft['id']);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('aircraft_compatibility.delete.success', [], 'aircraft_compatibility')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('aircraft_compatibility.delete.error', [], 'aircraft_compatibility')
            );
        }

        return $this->redirectToRoute('aircraft_home');
    }
}
