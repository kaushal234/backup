<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Repository\Directory\PeopleRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SalesForecastCountByASMByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly TokenStorageInterface $tokenStorage;
    private readonly PeopleRepository $peopleRepository;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(TokenStorageInterface $tokenStorage, PeopleRepository $peopleRepository, IriConverterInterface $iriConverter)
    {
        $this->tokenStorage = $tokenStorage;
        $this->peopleRepository = $peopleRepository;
        $this->iriConverter = $iriConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'asm.id' !== $x || 'factory.name' !== $y || null === ($token = $this->tokenStorage->getToken()) || !($user = $token->getUser()) instanceof People) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $mainQueryBuilder->getRootAliases()[0];

        $orStatements = $mainQueryBuilder->expr()->orX();

        if (isset($options['delinquent']) && $options['delinquent']) {
            $mainQueryBuilder->andWhere('o.delinquent = true');
        }

        $mainQueryBuilder->leftJoin(\sprintf('%s.asm', $alias), 'asm');
        $mainQueryBuilder->leftJoin(\sprintf('%s.factory', $alias), 'factory');

        $mainQueryBuilder->resetDQLPart('select');
        $mainQueryBuilder
            ->addSelect('COUNT(o) AS value')
            ->addSelect("CONCAT(asm.firstname, ' ', asm.lastname) AS x")
            ->addSelect('factory.name AS y')
            ->addSelect('asm.id AS asm_id')
            ->addSelect('factory.id AS factory_id')
            ->addOrderBy('x')
        ;

        if (isset($options['closedAt-after'])) {
            $mainQueryBuilder
                ->andWhere('o.closedAt >= :after')
                ->setParameter('after', new \DateTime($options['closedAt-after']))
            ;
        } else {
            $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->in(\sprintf('%s.status', $alias), SalesForecast::OPEN_STATUSES));
        }

        if (isset($options['asm'])) {
            $asm = $this->iriConverter->getResourceFromIri($options['asm']);
            $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->eq(\sprintf('%s.asm', $alias), ':asm'));
            $mainQueryBuilder->setParameter('asm', $asm);
        } else {
            $mainQueryBuilder->andWhere($mainQueryBuilder->expr()->in(\sprintf('%s.asm', $alias), ':subordinates'));
            $subordinates = $this->peopleRepository->getSubordinates($user, 3);
            $mainQueryBuilder->setParameter('subordinates', $subordinates + [$user]);
        }

        $mainQueryBuilder->andWhere($orStatements);

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            null,
            null
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(People::class, 'asm_id')
            ->setY(Location::class, 'factory_id')
            ->generate()
        );
    }
}
