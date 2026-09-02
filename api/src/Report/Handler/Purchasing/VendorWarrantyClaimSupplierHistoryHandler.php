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
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VendorWarrantyClaimSupplierHistoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly Security $security,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'supplier_history' !== $x) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(vwc) AS value')
            ->addSelect("DATE_FORMAT(vwc.createdAt,'%Y') AS y")
            ->addSelect('vwc_status.name AS x')
            ->addSelect('vwc_status.id AS status_id')
            ->from(VendorWarrantyClaim::class, 'vwc')
            ->leftJoin(VendorWarrantyClaimStatus::class, 'vwc_status', Join::WITH, 'vwc_status.id = vwc.status')
            ->where('vwc.createdAt > :three_years_ago')
            ->groupBy('y, x')
            ->orderBy('x, y')
            ->setParameters(new ArrayCollection([
                new Parameter('three_years_ago', new \DateTime('first day of January -36 months')),
            ]));

        if (null !== ($options['rejected'] ?? null) && '0' !== $options['rejected']) {
            $queryBuilder
                ->andWhere('(vwc_status.name = :statusRejected OR vwc.accepted = true)')
                ->setParameter('statusRejected', VendorWarrantyClaimStatus::CLOSED_VENDOR_REJECTED)
            ;
        }

        if (isset($options['location'])) {
            $location = $this->iriConverter->getResourceFromIri($options['location']);
            if (!$location instanceof Location) {
                return null;
            }

            $queryBuilder
                ->andWhere('vwc.location = :location')
                ->setParameter(':location', $location)
            ;
        }

        $user = $this->security->getUser();
        if ($user instanceof People) {
            if (null !== ($options['supplierNumber'] ?? null)) {
                $queryBuilder->andWhere('vwc.supplierNumber = :supplierNumber');
                $queryBuilder->setParameter('supplierNumber', $options['supplierNumber']);
            }
        }

        if ($user instanceof VendorUser) {
            $suppliers = isset($options['supplierNumber']) ? [$options['supplierNumber']] : $user->getBusinessPartnerCodes();

            if (isset($options['supplierNumber']) && !\in_array($options['supplierNumber'], $user->getBusinessPartnerCodes(), true)) {
                throw new AccessDeniedHttpException();
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->in('vwc.supplierNumber', ':suppliers'))
                ->setParameter(':suppliers', $suppliers)
            ;
        }

        $provider = new ReportDataProvider(
            $results = (new QueryBuilderExtractor($queryBuilder))(),
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(VendorWarrantyClaimStatus::class, 'status_id')
            ->generate()
        );
    }

    public function isGranted(?object $user = null): bool
    {
        return $user instanceof People || $user instanceof VendorUser;
    }
}
