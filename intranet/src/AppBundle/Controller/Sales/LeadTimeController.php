<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\LeadTimeFilterType;
use AppBundle\Form\Type\Sales\Catalogue\LeadTimeBatchType;
use AppBundle\Report\MatrixReportDataFormatter;
use AppBundle\Security\RoleProvider\AclProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: 'sales/catalogue/lead_times', defaults: ['alvest_module' => 'SLT', 'moduleDomain' => 'lead_time'])]
class LeadTimeController extends AbstractController
{
    final public const RESOURCE_URL = 'lead_times';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, AclProvider::class]);
    }

    #[Route(path: '', name: 'lead_time_home', methods: ['GET', 'POST'])]
    #[Template('/sales/catalogue/lead_time/home.html.twig')]
    public function home(Request $request)
    {
        $allowedLocations = $this->getAllowedLocations();

        $parameters = ['productFamily.hidden' => false];
        if ($filtered = (bool) $request->query->get('my-locations', false)) {
            $parameters['factory'] = array_column($allowedLocations['hydra:member'], '@id');
        }

        $leadTimeForm = $this->createForm(LeadTimeFilterType::class);

        $leadTimeForm->handleRequest($request);
        if ($leadTimeForm->isSubmitted() && $leadTimeForm->isValid()) {
            $parameters = array_merge(['productFamily.hidden' => false], $leadTimeForm->getData());
        }
        $leadTimes = $this->container->get(Client::class)->findBy(self::RESOURCE_URL, $parameters);

        $leadTimesMatrixData = MatrixReportDataFormatter::transform(
            $leadTimes->getSimpleArrayCopy(),
            '[productFamily][name]',
            '[factory][name]',
            static function (array $value): array {
                return [
                    'weeks' => $value['weeks'],
                    'previousValue' => $value['previousValue'],
                    'description' => $value['description'],
                    'updatedAt' => $value['updatedAt'] ?? '',
                    'updatedBy' => $value['updatedBy'] ?? '',
                ];
            });

        $familyIris = [];
        $factoryIris = [];
        foreach ($leadTimes as $leadTime) {
            $familyIris[$leadTime['productFamily']['name']] = $leadTime['productFamily']['@id'];
            $factoryIris[$leadTime['factory']['name']] = $leadTime['factory']['@id'];
        }

        $reportHotSfr = $this->container->get(Client::class)->get('/reports/resource=/sales/sales_forecasts;x=product.family.name;y=factory.name', ['query' => ['options' => ['hot' => true]]]);
        $reportQuantityHotSfr = $this->container->get(Client::class)->get('/reports/resource=/sales/sales_forecasts;x=product.family.name;y=factory.name', ['query' => ['options' => ['hot' => true, 'quantity' => true]]]);

        return [
            'leadTimesMatrixData' => $leadTimesMatrixData,
            'reportHotSfr' => $reportHotSfr,
            'reportQuantityHotSfr' => $reportQuantityHotSfr,
            'allowedLocations' => $allowedLocations['hydra:member'],
            'familyIris' => $familyIris,
            'factoryIris' => $factoryIris,
            'filtered' => $filtered,
            'leadTimeForm' => $leadTimeForm->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'lead_time_show', methods: ['GET', 'POST', 'DELETE'])]
    #[Route(path: '/{id}/edit', name: 'lead_time_edit', methods: ['GET', 'POST', 'DELETE'])]
    #[Template('/sales/catalogue/lead_time/write.html.twig')]
    #[IsGranted('FEATURE_LEAD_TIME_WRITE')]
    public function write(
        #[ApiValueResolverAttribute] ApiData $location,
        #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_families', 'filters' => ['pagination' => false, 'hidden' => false]])] HydraCollection $productFamilies,
        Request $request,
    ) {
        $allowedLocations = $this->getAllowedLocations();

        $leadTimes = $this->container->get(Client::class)->findBy(self::RESOURCE_URL, ['factory' => $location->getIri()]);

        $indexedLeadTimes = [];
        foreach ($leadTimes->getSimpleArrayCopy() as $leadTime) {
            $indexedLeadTimes[$leadTime['productFamily']['@id']] = $leadTime;
        }

        $data = ['leadTimes' => []];
        foreach ($productFamilies->getSimpleArrayCopy() as $family) {
            $familyId = Iri::id($family['@id']);
            $data['leadTimes'][$familyId] = ['productFamily' => $family['@id'], 'familyName' => $family['name']];
            if ($leadTime = $indexedLeadTimes[$family['@id']] ?? false) {
                $data['leadTimes'][$familyId] += [
                    'weeks' => $leadTime['weeks'],
                    'description' => $leadTime['description'],
                ];
            }
        }

        $form = $this
            ->container
            ->get('form.factory')
            ->createNamed(
                'lead_time',
                LeadTimeBatchType::class,
                $data
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $batch = [];
            $data = $form->getData();
            foreach ($data['leadTimes'] as $leadTime) {
                $leadTime['factory'] = $location->getIri();
                if ($existingLeadTime = $indexedLeadTimes[$leadTime['productFamily']] ?? false) {
                    $leadTime['@id'] = $existingLeadTime['@id'];
                    if (true === $leadTime['reset']) {
                        try {
                            $this->container->get(Client::class)->remove(self::RESOURCE_URL, Iri::id($leadTime['@id']));
                        } catch (ClientException $e) {
                            $this->addFlash(
                                'error',
                                $this->container->get(TranslatorInterface::class)->trans('lead_time.edit.error', [], 'catalogue')
                            );
                        }
                        continue;
                    }
                }

                if (null !== $leadTime['weeks'] && null !== $leadTime['description']) {
                    unset($leadTime['reset']);
                    $batch[] = $leadTime;
                }
            }

            $payload = ['leadTimes' => $batch, 'fullUpdate' => $data['fullUpdate']];

            try {
                $this->container->get(Client::class)->save('manufacturing/lead_times/batch', $payload);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('lead_time.edit.success', [], 'catalogue')
                );

                return $this->redirectToRoute('lead_time_home');
            } catch (ClientException $e) {
                $errors = json_decode($e->getResponse()->getContent(false), true);
                foreach ($errors['violations'] as $error) {
                    $this->addFlash(
                        'warning',
                        $error['message']
                    );
                }
            }
        }

        return [
            'families' => $productFamilies,
            'location' => $location,
            'form' => $form->createView(),
            'allowedLocations' => $allowedLocations['hydra:member'],
        ];
    }

    private function getAllowedLocations(): array
    {
        if ($this->isGranted('ACL_SUPERUSER') || $this->isGranted('MOO_SLT')) {
            return $this->container->get(Client::class)->get(
                'locations',
                ['query' => ['capability.factory' => true, 'normalizationGroupsOverride' => ['location_public']]]
            );
        }

        $locations = preg_filter('/^.*ROLE_(?:PSA|PSE|PSM|COO)_(\d+)$/', '/locations/$1', $this->container->get(AclProvider::class)->loadRolesByUser($this->getUser()));

        $allowedLocations = array_unique($locations ?? []);

        return $this->container->get(Client::class)->get(
            'locations',
            ['query' => [
                'id' => array_map(static fn (string $location) => Iri::id($location), $allowedLocations),
                'capability.factory' => true,
                'normalizationGroupsOverride' => ['location_public'],
            ],
            ]
        );
    }
}
