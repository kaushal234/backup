<?php

declare(strict_types=1);

namespace App\DataProvider\Service;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Service\TechnicianOnCall\ClosureTimeReport;
use App\Repository\Service\TechnicianOnCallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ClosureTimeReportDataProvider implements ProviderInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            DenormalizerInterface::class,
            TechnicianOnCallRepository::class,
            RequestStack::class,
        ];
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var TechnicianOnCallRepository $technicianOnCallRepository */
        $technicianOnCallRepository = $this->container->get(TechnicianOnCallRepository::class);

        $request = $this->container->get(RequestStack::class)->getCurrentRequest();

        $from = $request->query->get('from') ? (new \DateTimeImmutable($request->query->get('from')))->setTime(0, 0) : null;
        $salesServiceOrganisation = $request->query->get('salesServiceOrganisation');

        $startOfMonth = $from ?? (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $startOfNextMonth = (new \DateTimeImmutable('first day of next month'))->setTime(0, 0);

        $currentMonthStats = $technicianOnCallRepository->getResolutionDelaysStats($startOfMonth, $startOfNextMonth, $salesServiceOrganisation, true);

        $startLastYear = $startOfMonth->modify('first day of -12 months');
        $totalYear = $technicianOnCallRepository->getResolutionDelaysStats($startLastYear, $startOfNextMonth, $salesServiceOrganisation);

        $numberResolvedLastYear = $technicianOnCallRepository->countResolved($startLastYear, $startOfNextMonth, $salesServiceOrganisation);

        $yearly = [];
        foreach ($totalYear as $row) {
            $yearly[$row['delay']] = (int) $row['total'];
        }

        $totalSolvedThisMonth = array_sum(array_column($currentMonthStats, 'total'));

        $results = [];
        foreach ($currentMonthStats as $data) {
            $delay = $data['delay'];
            $total = $data['total'];
            $withoutCustomerServiceRecord = $data['without_csr'];
            $yearPercent = isset($yearly[$delay]) && $numberResolvedLastYear > 0 ? ($yearly[$delay] / $numberResolvedLastYear * 100) : 0;

            $monthExpected = $totalSolvedThisMonth * $yearPercent / 100;
            $delta = $monthExpected > 0 ? round((($total - $monthExpected) / $monthExpected) * 100, 2) : 0;
            $remotePercent = $total > 0 ? round(($withoutCustomerServiceRecord / $total) * 100, 2) : 0;

            $results[] = [
                'delay' => $delay,
                'numberOfTechnicianOnCalls' => $total,
                'percentRemoteSolved' => $remotePercent,
                'deltaWithYear' => $delta,
                'monthExpected' => $monthExpected,
            ];
        }

        /** @var DenormalizerInterface $serializer */
        $serializer = $this->container->get(DenormalizerInterface::class);

        return $serializer->denormalize($results, ClosureTimeReport::class.'[]');
    }
}
