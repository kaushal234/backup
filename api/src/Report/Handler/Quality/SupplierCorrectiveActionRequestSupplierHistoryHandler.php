<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SupplierCorrectiveActionRequestSupplierHistoryHandler implements ReportHandlerInterface
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
        if (SupplierCorrectiveActionRequest::class !== $resourceClass || 'supplier_history' !== $x) {
            return null;
        }
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(scar) AS value')
            ->addSelect("DATE_FORMAT(scar.createdAt,'%Y') AS y")
            ->addSelect('scar.status AS x')
            ->from(SupplierCorrectiveActionRequest::class, 'scar')
            ->where('scar.createdAt > :three_years_ago')
            ->groupBy('y, x')
            ->orderBy('x, y')
            ->setParameters(new ArrayCollection([
                new Parameter('three_years_ago', new \DateTime('first day of January -36 months')),
            ]));
        if (isset($options['factory'])) {
            $factory = $this->iriConverter->getResourceFromIri($options['factory']);
            if (!$factory instanceof Location) {
                return null;
            }

            $queryBuilder
                ->andWhere('scar.factory = :factory')
                ->setParameter(':factory', $factory)
            ;
        }

        $user = $this->security->getUser();
        if ($user instanceof People) {
            if (null !== ($options['supplierNumber'] ?? null)) {
                $queryBuilder->andWhere('scar.supplierNumber = :supplierNumber');
                $queryBuilder->setParameter('supplierNumber', $options['supplierNumber']);
            }
        }

        if ($user instanceof VendorUser) {
            $suppliers = isset($options['supplierNumber']) ? [$options['supplierNumber']] : $user->getBusinessPartnerCodes();

            if (isset($options['supplierNumber']) && !\in_array($options['supplierNumber'], $user->getBusinessPartnerCodes(), true)) {
                throw new AccessDeniedHttpException();
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->in('scar.supplierNumber', ':suppliers'))
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
