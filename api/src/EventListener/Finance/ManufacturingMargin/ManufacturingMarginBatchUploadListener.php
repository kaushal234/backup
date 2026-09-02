<?php

declare(strict_types=1);

namespace App\EventListener\Finance\ManufacturingMargin;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Dto\Finance\ManufacturingMarginBatch;
use App\Entity\Directory\Location;
use App\Entity\Finance\ManufacturingMargin;
use App\Notifier\Finance\ManufacturingMarginNotifier;
use App\Repository\Directory\LocationRepository;
use LegacyBundle\Manager\ManufacturingMarginManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ManufacturingMarginBatchUploadListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onPostUpload(ViewEvent $event)
    {
        $manufacturingMarginBatch = $event->getControllerResult();

        if (!$manufacturingMarginBatch instanceof ManufacturingMarginBatch || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $report = [];
        $factory = null;
        $exportedAt = null;
        /** @var ManufacturingMargin $manufacturingMargin */
        foreach ($manufacturingMarginBatch->getMargins() as $manufacturingMargin) {
            $linkedSOL = $this->serviceLocator->get(ManufacturingMarginManager::class)->getSalesOrderLineInformation($manufacturingMargin);

            foreach ($linkedSOL as $key => $property) {
                if (null === $property) {
                    unset($linkedSOL[$key]);
                }
            }

            if (empty($linkedSOL) || !isset($linkedSOL['est_dir_margin_per'])) {
                continue;
            }

            $standardDirectMarginPercentage = $manufacturingMargin->getStandardDirectMarginPercentage();
            $actualDirectMarginPercentage = $manufacturingMargin->getActualDirectMarginPercentage();
            $estimatedDirectMarginPercentage = $linkedSOL['est_dir_margin_per'];

            if (
                (!$this->checkGap($standardDirectMarginPercentage - $actualDirectMarginPercentage)
                || !$this->checkGap($standardDirectMarginPercentage - $estimatedDirectMarginPercentage))
                && (!$this->checkGap($standardDirectMarginPercentage - $actualDirectMarginPercentage)
                    || !$this->checkGap($actualDirectMarginPercentage - $estimatedDirectMarginPercentage))
                && (!$this->checkGap($standardDirectMarginPercentage - $estimatedDirectMarginPercentage)
                    || !$this->checkGap($actualDirectMarginPercentage - $estimatedDirectMarginPercentage))
            ) {
                continue;
            }

            $report[] = [
                'serialNumber' => null !== $manufacturingMargin->getEquipmentRecord() ? $manufacturingMargin->getEquipmentRecord()->getSerialNumber() : '',
                'solId' => $linkedSOL['sol_id'] ?? '',
                'sso' => $linkedSOL['sso_fullname'] ?? '',
                'factory' => $linkedSOL['erp_fullname'] ?? '',
                'buyer' => $linkedSOL['buyer'] ?? '',
                'model' => $linkedSOL['model'] ?? '',
                'actualDirectMarginPercentage' => $actualDirectMarginPercentage,
                'standardDirectMarginPercentage' => $standardDirectMarginPercentage,
                'estimatedDirectMarginPercentage' => $estimatedDirectMarginPercentage,
            ];

            if (!$factory instanceof Location && isset($linkedSOL['erp_fullname'])) {
                /** @var Location $factory */
                $factory = $this->serviceLocator->get(LocationRepository::class)->findOneBy(['name' => $linkedSOL['erp_fullname']]);
            }

            if (!$exportedAt instanceof \DateTimeInterface) {
                $exportedAt = $manufacturingMargin->getExportedAt();
            }
        }

        if ([] !== $report && null !== $factory) {
            $this->serviceLocator->get(ManufacturingMarginNotifier::class)->sendReport($report, $factory, $exportedAt);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPostUpload', EventPriorities::POST_WRITE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            ManufacturingMarginManager::class,
            LocationRepository::class,
            ManufacturingMarginNotifier::class,
        ];
    }

    private function checkGap(float $value, int $gap = 2): bool
    {
        return $value >= abs($gap);
    }
}
