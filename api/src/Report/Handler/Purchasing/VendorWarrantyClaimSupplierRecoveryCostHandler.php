<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VendorWarrantyClaimSupplierRecoveryCostHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly IriConverterInterface $iriConverter,
        private readonly Security $security,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'supplier_recovery_cost' !== $x) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select("CASE
			    WHEN vwc_status.name LIKE 'CLOSED%' THEN ROUND(SUM(vwc.actual_credit_amount), 2)
			    ELSE ROUND(SUM(vwc.requested_credit_amount), 2)
		        END AS value")
            ->addSelect("DATE_FORMAT(vwc.created_at,'%Y-%m') AS x")
            ->addSelect("CASE
                WHEN vwc_status.name LIKE 'CLOSED%' THEN 'Actual Credit Amt'
                WHEN vwc_status.name ='VENDOR_TO_RESPOND' THEN 'Supplier Credit Amt'
                ELSE 'Requested Credit Amt'
                END AS y")
            ->from('vendor_warranty_claims', 'vwc')
            ->leftJoin('vwc', 'vendor_warranty_claim_status', 'vwc_status', 'vwc_status.id = vwc.status_id')
            ->where('vwc_status.name != :pending')
            ->andWhere('vwc.created_at > :one_years_ago')
            ->groupBy('y, x')
            ->orderBy('x, y')
            ->setParameters([
                'pending' => VendorWarrantyClaimStatus::PENDING,
                'one_years_ago' => (new \DateTime('1 year ago'))->format('Y-m-d'),
            ]);

        $user = $this->security->getUser();
        if ($user instanceof People) {
            if (null !== ($options['supplierNumber'] ?? null)) {
                $queryBuilder->andWhere('vwc.supplier_number = :supplierNumber');
                $queryBuilder->setParameter('supplierNumber', $options['supplierNumber']);
            }
        }

        if ($user instanceof VendorUser) {
            $suppliers = isset($options['supplierNumber']) ? [$options['supplierNumber']] : $user->getBusinessPartnerCodes();

            if (isset($options['supplierNumber']) && !\in_array($options['supplierNumber'], $user->getBusinessPartnerCodes(), true)) {
                throw new AccessDeniedHttpException();
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->in('vwc.supplier_number', ':suppliers'))
                ->setParameter('suppliers', $suppliers, ArrayParameterType::STRING)
            ;
        }

        if (null !== ($options['location'] ?? null) && 'ALL' !== $options['location']) {
            $location = $this->iriConverter->getResourceFromIri($options['location']);
            if (!$location instanceof Location) {
                return null;
            }
            $queryBuilder
                ->andWhere('vwc.location_id = :location')
                ->setParameter('location', $location->getId(), ParameterType::INTEGER)
            ;
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();
        foreach ($results as &$result) {
            if (null === $result['value']) {
                $result['value'] = '0.00';
            }
        }

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }

    public function isGranted(?object $user = null): bool
    {
        return $user instanceof People || $user instanceof VendorUser;
    }
}
