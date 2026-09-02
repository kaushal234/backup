<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * This listener clean phone before validation.
 */
class PhoneCleaningListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function cleanUp(ViewEvent $event)
    {
        $subject = $event->getControllerResult();
        if (!$subject instanceof Location && !$subject instanceof Customer) {
            return;
        }

        $request = $event->getRequest();

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if ((null !== ($operationName = $operation->getName()) && false === !$operation->canValidate())
            || (!$request->isMethod(Request::METHOD_POST) && !$request->isMethod(Request::METHOD_PUT))) {
            return;
        }

        $properties = $this->getSupportedProperties($subject);
        $accessor = $this->serviceLocator->get(PropertyAccessorInterface::class);
        foreach ($properties as $property) {
            $value = $accessor->getValue($subject, $property);
            if (null !== $value && mb_strlen(mb_trim((string) $value)) > 0) {
                $phoneNumberUtil = $this->serviceLocator->get(PhoneNumberUtil::class);
                try {
                    $parsedPhone = $phoneNumberUtil->parse($value, PhoneNumberUtil::UNKNOWN_REGION);
                } catch (NumberParseException $numberParseException) {
                    throw new UnprocessableEntityHttpException(\sprintf('The number "%s" defined in "%s" is not valid.', $value, $property), $numberParseException);
                }
                $accessor->setValue(
                    $subject,
                    $property,
                    $phoneNumberUtil->format($parsedPhone, PhoneNumberFormat::INTERNATIONAL)
                );
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['cleanUp', EventPriorities::PRE_VALIDATE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            PhoneNumberUtil::class,
            PropertyAccessorInterface::class,
        ];
    }

    private function getSupportedProperties($subject): array
    {
        if ($subject instanceof Location) {
            return [
                'contact.telephone',
                'contact.fax',
                'contact.sparePartsTelephone',
                'contact.sparePartsFax',
                'contact.serviceHubTelephone',
            ];
        }
        if ($subject instanceof Customer) {
            return [
                'phone',
                'fax',
            ];
        }

        return [];
    }
}
