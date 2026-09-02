<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Filter\ReportOptionsFilter;
use App\Report\ReportGenerator;
use App\Repository\Report\ReportSnapshotRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ReportDataProvider implements ProviderInterface, ServiceSubscriberInterface
{
    final public const SNAPSHOT_OPTION = 'snapshot';
    private const SNAPSHOT_OPTION_NAME = 'snapshotDate';
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedServices(): array
    {
        return [
            ReportGenerator::class,
            ReportSnapshotRepository::class,
            DenormalizerInterface::class,
            NormalizerInterface::class,
        ];
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var string $resource */
        $resource = $uriVariables['resource'];
        /** @var string $x */
        $x = $uriVariables['x'];
        /** @var string $y */
        $y = $uriVariables['y'];

        $options = $context['filters'][ReportOptionsFilter::PARAMETER_NAME] ?? [];

        if (isset($context['filters'][ReportOptionsFilter::PARAMETER_NAME][self::SNAPSHOT_OPTION_NAME])) {
            /** @var \DateTime $date */
            $date = $this->container->get(DenormalizerInterface::class)->denormalize($options[self::SNAPSHOT_OPTION_NAME] ?? '', \DateTime::class);
            $date->setTime(0, 0);

            unset($options[self::SNAPSHOT_OPTION_NAME]);

            $resource = $this->container->get(ReportSnapshotRepository::class)->findSnapshot($resource, $x, $y, $options, $date);
            if (null !== $resource) {
                return $resource;
            }

            throw new NotFoundHttpException();
        }

        return $this->container->get(ReportGenerator::class)->getReport($resource, $x, $y, $options);
    }
}
