<?php

declare(strict_types=1);

namespace App\Command\Sales\SalesForecast;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\Change;
use App\Entity\Activity\Log;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;
use App\Entity\Sales\SalesForecast;
use App\Factory\SalesForecastSnapshotFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsCommand(name: 'tld:sales_forecast:past_snapshot')]
class SalesForecastSnapshotPastCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly PropertyAccessorInterface $propertyAccessor;
    private readonly IriConverterInterface $iriConverter;
    private readonly SalesForecastSnapshotFactory $salesForecastSnapshotFactory;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor, IriConverterInterface $iriConverter, SalesForecastSnapshotFactory $salesForecastSnapshotFactory)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->iriConverter = $iriConverter;
        $this->salesForecastSnapshotFactory = $salesForecastSnapshotFactory;

        $this
            ->setDescription('Generate past snapshots of Sales Forecasts based on logs :(')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'from date', '2019-02-18')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $salesForecastRepository = $this->entityManager->getRepository(SalesForecast::class);
        $logRepository = $this->entityManager->getRepository(Log::class);

        $propertiesClassMap = [
            'asm' => People::class,
            'buyer' => Customer::class,
            'endUser' => Customer::class,
            'thirdParty' => Customer::class,
            'product' => Product::class,
            'sso' => Location::class,
            'factory' => Location::class,
            'tier' => EmissionRating::class,
            'country' => Country::class,
        ];

        /** @var QueryBuilder $salesForecastQueryBuilder */
        $salesForecastQueryBuilder = $salesForecastRepository->createQueryBuilder('o')->addOrderBy('o.id', 'DESC');

        $oneWeekInterval = new \DateInterval('P1W');
        $period = new \DatePeriod((new \DateTime($input->getOption('from')))->setTime(0, 0), $oneWeekInterval, (new \DateTime('last Monday'))->add(\DateInterval::createFromDateString('1 day'))->setTime(0, 0));
        $weeks = array_reverse([...$period]);

        $firstResult = 0;
        while (!empty($salesForecasts = (clone $salesForecastQueryBuilder)->setMaxResults(100)->setFirstResult($firstResult)->getQuery()->getResult())) {
            $output->writeln(\sprintf('Current first result value is %s', $firstResult));
            /** @var SalesForecast $salesForecast */
            foreach ($salesForecasts as $salesForecast) {
                $closedAt = $salesForecast->getClosedAt();
                if (null !== $closedAt && $closedAt < $period->getStartDate()) {
                    continue;
                }

                $output->write(\sprintf('Creating snapshots for SFR#%s... ', $salesForecast->getId()));
                $iri = $this->iriConverter->getIriFromResource($salesForecast);
                /** @var Log[] $logs */
                $logs = $logRepository->findBy(['action' => Change::ACTION_UPDATE, 'resource' => $iri]);
                $previousDate = new \DateTime();

                $snapshots = [];

                // let's go back in time, week after week
                /** @var \DateTime $date */
                foreach ($weeks as $date) {
                    if ($salesForecast->getCreatedAt() >= $date) {
                        continue;
                    }

                    if (null !== $closedAt && $closedAt < $date) {
                        continue;
                    }

                    // get logs for the current week
                    $periodLogs = $this->filterLogsByDates($logs, $date, $previousDate);
                    $previousDate = $date;

                    foreach ($periodLogs as $log) {
                        foreach ($log->getChangeSet() as $property => $values) {
                            [$before] = $values;
                            $value = $before;

                            if ('salesForecastFiles' === $property) {
                                continue;
                            }

                            if ('price' === $property && null === $value && null !== $salesForecast->getPrice()) {
                                continue;
                            }

                            if ('margin' === $property && null === $value && null !== $salesForecast->getMargin()) {
                                continue;
                            }

                            // no need to have an advanced logic if the value to set is null
                            if (null !== $before) {
                                switch ($property) {
                                    // nothing to do, the value is a string/float/int/bool/...
                                    case 'status':
                                    case 'lastComment':
                                    case 'poster':
                                    case 'equoteId':
                                    case 'quantity':
                                    case 'customerSuccessPercentage':
                                    case 'successPercentage':
                                    case 'delinquent':
                                    case 'price':
                                    case 'margin':
                                        break;
                                        // datetimes
                                    case 'createdAt':
                                    case 'updatedAt':
                                    case 'lastCommentedAt':
                                    case 'closedAt':
                                    case 'estimatedSaleDate':
                                    case 'closureNotificationSentAt':
                                        $value = new \DateTime($before);
                                        break;
                                        // the only related entity which doesn't implement toString()
                                    case 'masterSalesForecast':
                                        try {
                                            $value = $this->iriConverter->getResourceFromIri($before);
                                        } catch (\Exception $exception) {
                                            throw new \InvalidArgumentException(\sprintf('Something went wrong trying to get the item for "%s" (property "%s")', $before, $property), 0, $exception);
                                        }
                                        break;
                                        // this property is using @LoggedName
                                    case 'Customer Purchase Percentage':
                                        $property = 'customerSuccessPercentage';
                                        break;
                                        // this property is using @LoggedName
                                    case 'Alvest Success Percentage':
                                        $property = 'successPercentage';
                                        break;
                                        // these related entities implement toString
                                    case 'asm':
                                    case 'buyer':
                                    case 'endUser':
                                    case 'thirdParty':
                                    case 'product':
                                    case 'sso':
                                    case 'factory':
                                    case 'tier':
                                    case 'country':
                                    case 'airport':
                                        $matches = [];
                                        // the logs need to be parsed; the asm, for example are like "Name, FirstName (Surname) (/people/12)"
                                        if (!preg_match_all('#^.*\(\/(?P<iri>.+)\)$#', (string) $before, $matches, \PREG_SET_ORDER)) {
                                            // if the regex doesn't match, let's try to fetch the item by its name (because most of the time that's what toString() returns)
                                            if (null !== $value = $this->entityManager->getRepository($propertiesClassMap[$property])->findOneBy(['name' => mb_trim((string) $before)])) {
                                                break;
                                            }

                                            // I'd rather stop and try again than having missing data
                                            throw new \InvalidArgumentException(\sprintf('Could not parse Iri from string "%s" for property "%s" and could not fetch it by its name using the repository, try again, have fun', $before, $property));
                                        }
                                        $iri = $matches[0]['iri'];
                                        // because tiers were renamed :(
                                        if (false !== mb_strpos($iri, 'tiers')) {
                                            $iri = str_replace('tiers', 'emission_ratings', $iri);
                                        }

                                        try {
                                            $value = $this->iriConverter->getResourceFromIri($iri);
                                        } catch (ItemNotFoundException $itemNotFoundException) {
                                            if (false !== mb_strpos($iri, 'sales/customers') || false !== mb_strpos($iri, 'sales/products')) {
                                                // the customer or the product has been deleted, so well, there's nothing I can do apart from resetting this value to null
                                                $value = null;
                                                break;
                                            }
                                            throw $itemNotFoundException;
                                        }
                                        break;
                                    default:
                                        // not handled, should not be triggered
                                        throw new \InvalidArgumentException(\sprintf('property %s not handled, kaboom', $property));
                                }
                            }

                            try {
                                $this->propertyAccessor->setValue($salesForecast, $property, $value);
                            } catch (\InvalidArgumentException $invalidArgumentException) {
                                // let's rape the property when the setter doesn't allow null
                                if (false === mb_strpos($invalidArgumentException->getMessage(), '"null" given')) {
                                    throw $invalidArgumentException;
                                }
                                $reflectionProperty = new \ReflectionProperty(SalesForecast::class, $property);
                                $reflectionProperty->setAccessible(true);
                                $reflectionProperty->setValue($salesForecast, null);
                            }
                        }
                    }

                    $snapshot = $this->salesForecastSnapshotFactory->createSnapshot($salesForecast);
                    $snapshot->setSnapshotCreatedAt($date);
                    $this->entityManager->persist($snapshot);
                    $snapshots[] = $snapshot;
                }

                // now that we're done playing with this object, let's reset it to prevent it to be accidentally persisted to the legacy DB (thank you Doctrine)
                $this->entityManager->refresh($salesForecast);

                $output->write(\sprintf("%s snapshots created\n", \count($snapshots)));
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $firstResult += 100;
        }

        $this->entityManager->flush();

        return 0;
    }

    /**
     * @param Log[] $logs
     *
     * @return Log[]
     */
    private function filterLogsByDates(array $logs, \DateTime $after, \DateTime $before): array
    {
        return array_filter($logs, static fn (Log $log) => $log->getCreatedAt() >= $after && $log->getCreatedAt() < $before);
    }
}
