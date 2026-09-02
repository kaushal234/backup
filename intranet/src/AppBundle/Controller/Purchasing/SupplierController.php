<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Purchasing\SupplierDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/suppliers', defaults: ['breadcrumb_label' => 'menu.suppliers.title', 'moduleDomain' => 'suppliers'])]
class SupplierController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const string RESOURCE_URL = 'purchasing/suppliers';
    public const string SUPPLIER_RANKING_URL = 'purchasing/supplier_ranking/';

    public const string VENDOR_WARRANTY_CLAIMS_URL = 'purchasing/vendor_warranty_claims';
    public const array VWC_OPEN_STATUSES = [
        'QA_ANALYSIS' => '/purchasing/vendor_warranty_claim_statuses/1',
        'PENDING' => '/purchasing/vendor_warranty_claim_statuses/2',
        'VENDOR_TO_RESPOND' => '/purchasing/vendor_warranty_claim_statuses/3',
        'REVIEW_VENDOR_RESPONSE' => '/purchasing/vendor_warranty_claim_statuses/4',
        'CREATE_PO' => '/purchasing/vendor_warranty_claim_statuses/5',
        'SHIP_TO_VENDOR' => '/purchasing/vendor_warranty_claim_statuses/6',
        'ISSUE_CREDIT_NOTE' => '/purchasing/vendor_warranty_claim_statuses/7',
        'REC_FROM_VENDOR' => '/purchasing/vendor_warranty_claim_statuses/8',
        'ISSUE_DEBIT_NOTE' => '/purchasing/vendor_warranty_claim_statuses/9',
        'VALIDATE_SCAR' => '/purchasing/vendor_warranty_claim_statuses/10',
    ];

    public const array NCR_OPEN_STATUSES = [
        'PENDING' => 'PENDING',
        'IN PROGRESS' => 'IN PROGRESS',
        'SUSPENDED' => 'SUSPENDED',
    ];

    public const array SCAR_OPEN_STATUSES = [
        'PENDING' => 'PENDING',
        'VENDOR TO FILL FORM' => 'VENDOR TO FILL FORM',
        'TLD TO REVIEW FORM' => 'TLD TO REVIEW FORM',
        'VALIDATION' => 'VALIDATION',
        'COMMERCIAL AGREEMENT' => 'COMMERCIAL AGREEMENT',
    ];

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/', name: 'suppliers_home', methods: ['GET', 'POST'])]
    #[Template('purchasing/supplier/list.html.twig')]
    public function list(DataTableFactoryInterface $dataTableFactory, Request $request)
    {
        $datatable = $dataTableFactory->create(SupplierDataTableType::class, self::RESOURCE_URL);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'suppliers' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'suppliers_show', methods: ['GET'])]
    #[Template('purchasing/supplier/show.html.twig')]
    public function show(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/suppliers'])]
        ApiData $supplier,
    ): array|RedirectResponse {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        try {
            // Getting Supplier Rankings
            $supplierRanking = $client->findOneBy(
                self::SUPPLIER_RANKING_URL.'supplier_rankings',
                ['supplier' => $supplier['@id']]
            );

            // Getting Vendor Warranty Claims based on the supplier code
            $vendorWarrantyClaims = $client->findBy(
                self::VENDOR_WARRANTY_CLAIMS_URL,
                ['supplierNumber' => $supplier['code']],
            );

            // Getting Non-Conformities based on the supplier code
            $nonConformities = $client->findBy(
                'quality/non_conformities',
                [
                    'supplierNumber' => $supplier['code'],
                ]
            );

            // Getting Supplier Correction action requests  based on the supplier code
            $scars = $client->findBy(
                'quality/supplier_corrective_action_requests',
                ['supplierNumber' => $supplier['code']]
            );

            // Check if the vendor partners exists based on the supplier code if not return empty
            try {
                $businessPartner = $client->get(\sprintf('ion/business_partners/%s', $supplier['code']));
                $vendorUsers = $businessPartner['contacts'] ?? [];
                $vendorUsers = $this->enrichVendorUsers($vendorUsers, $client);
            } catch (\Exception $e) {
                $vendorUsers = [];
            }

            return [
                'supplier' => $supplier,
                'supplierRanking' => $supplierRanking,
                'vendorWarrantyClaims' => [
                    'data' => $vendorWarrantyClaims,
                    'count' => $this->getCountByStatus($vendorWarrantyClaims, self::VWC_OPEN_STATUSES, 'status.name'),
                    'iris' => array_values(self::VWC_OPEN_STATUSES),
                ],
                'nonConformities' => [
                    'data' => $nonConformities,
                    'count' => $this->getCountByStatus($nonConformities, self::NCR_OPEN_STATUSES, 'status'),
                    'statuses' => array_values(self::NCR_OPEN_STATUSES), // plain strings for filter
                ],
                'scars' => [
                    'data' => $scars,
                    'count' => $this->getCountByStatus($scars, self::SCAR_OPEN_STATUSES, 'status'),
                    'statuses' => array_values(self::SCAR_OPEN_STATUSES),
                ],
                'vendorUsers' => $vendorUsers,
            ];
        } catch (\RangeException $e) {
            $this->addFlash('error', $translator->trans('messages.error.no_data_found', [], 'suppliers'));

            return $this->redirectToRoute('suppliers_show', ['id' => $supplier['id']]);
        }
    }

    private function enrichVendorUsers(array $vendorUsers, Client $client): array
    {
        $enrichedUsers = [];

        foreach ($vendorUsers as $vendorUser) {
            $email = $vendorUser['emailAddress'] ?? '';
            $vendorUserId = null;

            if (!empty($email)) {
                try {
                    $vendorUserList = $client->findBy('purchasing/vendor_users', [
                        'email' => $email,
                    ]);

                    if ($vendorUserList->count() > 0) {
                        $foundVendorUser = $vendorUserList->first();
                        $vendorUserId = (string) $foundVendorUser['id'];
                    }
                } catch (\Exception $e) {
                }
            }
            $enrichedUsers[] = [
                'email' => $email,
                'vendorUserId' => $vendorUserId ?? null,
                'fullName' => !empty($vendorUser['fullName'])
                    ? $vendorUser['fullName']
                    : (!empty($vendorUser['firstName']) || !empty($vendorUser['familyName'])
                        ? $vendorUser['firstName'].' '.$vendorUser['familyName']
                        : ''
                    ),
                'contactCode' => $vendorUser['contactCode'] ?? null,
            ];
        }

        return $enrichedUsers;
    }

    private function getCountByStatus(
        HydraCollection $collection,
        array $statuses,
        string $statusKey = 'status',
    ): int {
        $statuses = array_map('strtoupper', array_keys($statuses));

        $count = 0;
        foreach ($collection as $item) {
            $keys = explode('.', $statusKey);
            $status = $item;
            foreach ($keys as $key) {
                $status = $status[$key] ?? null;
                if (null === $status) {
                    break;
                }
            }

            if (!\is_string($status)) {
                continue;
            }

            if (\in_array(mb_strtoupper($status), $statuses, true)) {
                ++$count;
            }
        }

        return $count;
    }
}
