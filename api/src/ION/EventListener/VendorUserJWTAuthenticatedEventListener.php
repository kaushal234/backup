<?php

declare(strict_types=1);

namespace App\ION\EventListener;

use App\Entity\Purchasing\VendorUser;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Serializer\Denormalizer\MasterData\BusinessPartners\BusinessPartnerDenormalizer;
use App\ION\Serializer\Denormalizer\MasterData\EnterpriseModel\EmployeeDenormalizer;
use App\ION\Serializer\Denormalizer\MasterData\EnterpriseModel\Entities\DepartmentDenormalizer;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use App\Security\JWT\PayloadGenerator\VendorUserPayloadGenerator;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class VendorUserJWTAuthenticatedEventListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onJWTAuthenticated(JWTAuthenticatedEvent $event)
    {
        $user = $event->getToken()->getUser();

        if (!$user instanceof VendorUser) {
            return;
        }
        $payload = $event->getPayload();

        if (null === ($contact = $payload[VendorUserPayloadGenerator::PAYLOAD_BUSINESS_PARTNER_CONTACT])) {
            return;
        }

        $user->contact = $this->serviceLocator->get(DenormalizerInterface::class)->denormalize($contact, BusinessPartnerContact::class, null, [
            AbstractNormalizer::GROUPS => ['contact', 'business_partner', 'address', 'contact:item', 'category', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP],
            BusinessPartnerDenormalizer::ALREADY_CALLED => true,
            DepartmentDenormalizer::ALREADY_CALLED => true,
            EmployeeDenormalizer::ALREADY_CALLED => true,
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_AUTHENTICATED => ['onJWTAuthenticated'],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            DenormalizerInterface::class,
        ];
    }
}
