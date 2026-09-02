<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Parts\VendorWarrantyClaimPart;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VendorWarrantyClaimPartFailureHistoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly Security $security,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'part_failure_history' !== $x) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(vwc) AS value')
            ->addSelect("DATE_FORMAT(vwc.createdAt,'%Y') AS y")
            ->addSelect('vwc_part.partNumber AS x')
            ->from(VendorWarrantyClaim::class, 'vwc')
            ->leftJoin(VendorWarrantyClaimPart::class, 'vwc_part', Join::WITH, 'vwc_part.vendorWarrantyClaim = vwc')
            ->where('vwc.createdAt > :three_years_ago')
            ->andWhere('vwc_part.partNumber IS NOT NULL')
            ->groupBy('vwc_part.partNumber, y')
            ->setParameters(new ArrayCollection([
                new Parameter('three_years_ago', new \DateTime('first day of January -36 months')),
            ]));

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

        return new ReportDataProvider(
            $results = (new QueryBuilderExtractor($queryBuilder))(),
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }

    public function isGranted(?object $user = null): bool
    {
        return $user instanceof People || $user instanceof VendorUser;
    }
}
