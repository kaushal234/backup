<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use AppBundle\Form\Type\Parts\DashboardFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/parts/dashboard', defaults: ['alvest_module' => 'SPH', 'moduleDomain' => 'parts_dashboard'])]
class DashboardController extends AbstractController
{
    public const LOCATIONS = [
        '100001' => 'Farmingdale',
        '104081' => 'LAX2',
        '105102' => 'ORD',
        '105651' => 'GILROY',
        '112811' => 'ATL',
        '115139' => 'DFW',
        '115589' => 'LAX1',
        '115886' => 'YYZ',
        '115887' => 'YUL',
        '115888' => 'YVR',
        '122958' => 'HKG',
        '125772' => 'JFK',
        '126415' => 'AMS',
        '127647' => 'CDG',
        '131093' => 'MEM2',
        '132143' => 'LGA',
        '133018' => 'MEM1',
        '133020' => 'DTW',
        '133022' => 'MSP',
        '133751' => 'PrestonUK',
        '133773' => 'LGWUK1',
        '133776' => 'LGWUK2',
        '133780' => 'LHR',
        '133784' => 'MANUK2',
        '133788' => 'MANUK1',
        '138533' => 'MIA2',
        '138534' => 'MIA1',
    ];

    public function __construct(
        private readonly Client $client,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
        private readonly FileStreamedResponseFactory $fileStreamedResponseFactory,
    ) {
    }

    #[Route(path: '/', name: 'parts_dashboard_home', methods: ['GET', 'POST'])]
    #[Template('parts/dashboard/view.html.twig')]
    #[IsGranted('FEATURE_PARTS_DASHBOARD_VIEW')]
    public function index(Request $request)
    {
        $formFilter = $this->createForm(DashboardFilterType::class);

        $formFilter->handleRequest($request);

        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $data = $formFilter->getData();
            $formFilterSearch = $formFilter->get('submit_search');
            $formShowShipments = $formFilter->get('submit_show_shipments');

            if ($formFilterSearch instanceof ClickableInterface && $formFilterSearch->isClicked()) {
                return $this->redirectToRoute('parts_dashboard_view', [
                    'partNumber' => $data['part_number'],
                ]);
            }
            if ($formShowShipments instanceof ClickableInterface && $formShowShipments->isClicked()) {
                return $this->redirect($request->getBaseUrl().'/parts/parts.php?m[0]=&m[1]=bySearch&erp='.$data['location'].'&item='.$data['part_number']);
            }
        }

        return [
            'formFilter' => $formFilter->createView(),
            'partNumber' => '',
            'fullView' => false,
        ];
    }

    #[Route(path: '/{partNumber}/images/{key}', name: 'parts_dashboard_picture', methods: 'GET')]
    #[IsGranted('FEATURE_PARTS_DASHBOARD_VIEW')]
    public function showPicture(string $partNumber, int $key): StreamedResponse
    {
        return $this
            ->fileStreamedResponseFactory
            ->create(\sprintf('inventories/%s/images/%d', $partNumber, $key))
        ;
    }

    #[Route(path: '/{partNumber}', name: 'parts_dashboard_view', methods: 'GET', requirements: ['partNumber' => '^(?!sage-inventory).+'])]
    #[Template('parts/dashboard/view.html.twig')]
    #[IsGranted('FEATURE_PARTS_DASHBOARD_VIEW')]
    public function view(string $partNumber)
    {
        $fullView = $this->authorizationChecker->isGranted('SUBDIVISION_FEATURE_PARTS_DASHBOARD_FULL_VIEW');
        $formFilter = $this->createForm(DashboardFilterType::class, ['part_number' => $partNumber], ['full_view' => $fullView]);

        $warehouses = [];
        $inventory = [];
        $textItems = [];
        $mipDetail = [];
        $mips = [];
        try {
            $inventories = $this->client->get("ion/inventories/project=;item=$partNumber", ['query' => [
                'includeInEnterprisePlanning' => true,
            ]]);
            // TEXT
            $textItems = array_filter(array_column($inventories['siteItems'], 'textItem'));

            uasort($textItems, static function ($textItemA, $textItemB) {
                return (int) $textItemA['site'] <=> (int) $textItemB['site'];
            });

            // MIP DETAIL
            $mipDetail = [
                'pilot' => $inventories['productLine'],
                'category' => null !== ($productClass = $inventories['productClass']) ? \sprintf('%s - %s', $productClass['code'], $productClass['name']) : '',
                'multiplier' => $inventories['multiplier'],
                'pmoc' => $inventories['pmoc'],
            ];

            // MIP
            foreach ($inventories['priceBookLines'] as $priceBookLine) {
                $mips[] = [
                    'value' => $priceBookLine['value'],
                    'currency' => $priceBookLine['currency'],
                    'effectiveDate' => $priceBookLine['effectiveDate'],
                    'expiryDate' => $priceBookLine['expiryDate'],
                ];
            }

            // PARTDASHBOARD
            foreach ($inventories['siteItems'] as $siteItem) {
                $warehouses[] = array_reduce($siteItem['warehouses'], static function (array $carry, array $warehouseData) use ($siteItem, $inventories) {
                    unset($siteItem['warehouses']);
                    $carry[] = array_merge($warehouseData, $siteItem, [
                        'item' => $inventories['item'],
                        'unitOfMeasure' => $inventories['unitOfMeasure'],
                        'description' => $inventories['description'],
                    ]);

                    return $carry;
                }, []);
            }
            $inventory = array_merge(...$warehouses);
        } catch (ClientException $e) {
            $formFilter->addError(new FormError($this->translator->trans('ion.error', [], 'ion')));
        } catch (\Exception $e) {
        }

        return [
            'formFilter' => $formFilter->createView(),
            'partNumber' => $partNumber,
            'description' => $inventories['description'] ?? '',
            'pictures' => $inventories['pictures'] ?? [],
            'inventory' => $inventory,
            'textItems' => $textItems,
            'mipDetail' => $mipDetail,
            'mips' => $mips,
            'fullView' => $fullView,
        ];
    }

    #[Route(path: '/sage-inventory/{partNumber}', name: 'sage_inventory_view', methods: 'GET', requirements: ['partNumber' => '.+'])]
    #[Template('parts/sage_inventory/view.html.twig')]
    #[IsGranted('FEATURE_PARTS_DASHBOARD_VIEW')]
    public function sageInventoryView(string $partNumber): array|RedirectResponse
    {
        try {
            $sageInventory = $this->client->get('/sage/sage_parts/'.$partNumber);
        } catch (ClientException $exception) {
            $this->addFlash(
                'error',
                $exception->getMessage()
            );

            return $this->redirectToRoute('parts_dashboard_view', [
                'partNumber' => $partNumber,
            ]);
        }
        $sageInventoryByLocation = [];
        foreach ($sageInventory['locations'] as $item) {
            $sageInventoryByLocation[] = [
                'pn' => $sageInventory['alvestId'],
                'sage_pn' => $sageInventory['sageId'],
                'description' => $sageInventory['description'],
                'sage_uid' => $item['name'],
                'sage_location_name' => \array_key_exists($item['name'], self::LOCATIONS) ? self::LOCATIONS[$item['name']] : '-',
                'currency' => $item['unitPrice']['currency'],
                'price' => $item['unitPrice']['price'],
                'on_hand' => $item['onHand']['quantity'],
                'on_hand_unit' => $item['onHand']['unit'],
                'on_order' => $item['onOrder']['quantity'],
                'on_order_unit' => $item['onOrder']['unit'],
            ];
        }

        return [
            'sageInventoryByLocation' => $sageInventoryByLocation,
        ];
    }
}
