<?php

declare(strict_types=1);

namespace App\Manager\Service;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerServiceRecordsManager implements ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function logToComment($change, $object): array
    {
        $commentParts = [];

        foreach ($change as $key => $changeSet) {
            if (null === $changeSet[1]) {
                return [];
            }

            if ($changeSet[1] instanceof \DateTime) {
                $changeSet[1] = $changeSet[1]->format('Y-m-d H:i:s');
            }

            switch ($key) {
                case 'description':
                    null !== $changeSet[0] ?
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.description', ['%description%' => $changeSet[1]], 'customer_service_record') :
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.description', ['%description%' => $changeSet[1]], 'customer_service_record');
                    break;
                case 'hourmeter':
                    if (null !== $changeSet[0]) {
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.hourmeter', ['%hourmeter[0]%' => $changeSet[0], '%hourmeter[1]%' => $changeSet[1]], 'customer_service_record');
                    }
                    break;
                case 'airport':
                    null !== $changeSet[0] ?
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.airport', ['%airport[0]%' => $changeSet[0], '%airport[1]%' => $changeSet[1]], 'customer_service_record') :
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.airport', ['%airport%' => $changeSet[1]], 'customer_service_record');
                    break;
                case 'plannedAt':
                    null !== $changeSet[0] ?
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.plannedAt', ['%plannedAt[0]%' => $changeSet[0]->format('Y-m-d H:i:s'), '%plannedAt[1]' => $changeSet[1]], 'customer_service_record') :
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.plannedAt', ['%plannedAt%' => $changeSet[1]], 'customer_service_record');
                    break;
                case 'leader':
                    null !== $changeSet[0] ?
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.leader', ['%leader[0]%' => $changeSet[0], '%leader[1]%' => $changeSet[1]], 'customer_service_record') :
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.leader', ['%leader%' => $changeSet[1]], 'customer_service_record');
                    break;
                case 'equipmentRecord':
                    null !== $changeSet[0] ?
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.equipmentRecord', ['%sn[0]%' => $changeSet[0], '%sn[1]%' => $changeSet[1]], 'customer_service_record') :
                        $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.equipmentRecord', ['%sn%' => $changeSet[1]->getSerialNumber()], 'customer_service_record');

                    break;
                case 'status':
                    if ($object instanceof Intervention) {
                        switch ($changeSet[1]) {
                            case 'PENDING':
                                $commentParts[] = '<i class="fa-solid fa-hourglass-half"></i>  PENDING';
                                break;
                            case 'FAILED_ASSIGNED':
                                $commentParts[] = '<i class="fa-solid fa-rotate-exclamation"></i>  FAILED ASSIGNED';
                                break;
                            case 'STARTED':
                                $commentParts[] = '<i class="fa-solid fa-flag"></i>  STARTED';
                                break;
                            case 'SOLVED':
                                $commentParts[] = '<i class="fa-solid fa-check"></i>  SOLVED';
                                break;
                            case 'TO_CONTINUE':
                                $commentParts[] = '<i class="fa-solid fa-circle-exclamation"></i>  TO CONTINUE';
                                break;
                            default:
                                break;
                        }
                    } else {
                        null !== $changeSet[0] ?
                            $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.change.status', ['%status[0]%' => $changeSet[0], '%status[1]%' => $changeSet[1]], 'customer_service_record') :
                            $commentParts[] = $this->serviceLocator->get(TranslatorInterface::class)->trans('log.status', ['%status%' => $changeSet[1]], 'customer_service_record');
                    }
                    break;
                default:
                    break;
            }
        }

        return $commentParts;
    }

    public static function getSubscribedServices(): array
    {
        return [
            TranslatorInterface::class,
        ];
    }
}
