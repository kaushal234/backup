<?php

declare(strict_types=1);

namespace App\Security\JWT\PayloadGenerator;

use App\Entity\Purchasing\VendorUser;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerContactManager;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\Serializer\Normalizer\UserNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VendorUserPayloadGenerator implements PayloadGeneratorInterface
{
    final public const PAYLOAD_BUSINESS_PARTNER_CONTACT = 'businessPartnerContact';
    final public const PAYLOAD_EVENDORS_ACCESS = 'isGrantedEvendorsAccess';

    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly BusinessPartnerContactManager $contactManager,
        private readonly BusinessPartnerManager $businessPartnerManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function generate(array &$payload, ?UserInterface $user = null): void
    {
        if (!$user instanceof VendorUser) {
            return;
        }

        $contact = $this->contactManager->findByErpIdentifier($user->getErpIdentifier());
        $payload[self::PAYLOAD_BUSINESS_PARTNER_CONTACT] = null;
        $businessPartnerCodes = [];

        if (null !== $contact) {
            if ('' !== $contact->firstName && '' !== $contact->familyName && ($contact->firstName !== $user->getFirstname() || $contact->familyName !== $user->getLastname())) {
                $user
                    ->setFirstname($contact->firstName)
                    ->setLastname($contact->familyName);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
            foreach ($contact->getBusinessPartners() as $businessPartner) {
                if (!\in_array($businessPartner->code, $businessPartnerCodes, true)) {
                    $businessPartnerCodes[] = $businessPartner->code;
                    $businessPartnerWithEmail = $this->businessPartnerManager->findSupplier($businessPartner->code);

                    if (!$businessPartnerWithEmail instanceof BusinessPartner) {
                        continue;
                    }
                    $contact->removeBusinessPartner($businessPartner)->addBusinessPartner($businessPartnerWithEmail);
                }
            }

            $payload[self::PAYLOAD_BUSINESS_PARTNER_CONTACT] = (array) $this->normalizer->normalize(
                $contact,
                'json',
                [
                    AbstractNormalizer::GROUPS => [PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'expose_legacy', 'file:light'],
                    'jsonld_has_context' => false,
                    UserNormalizer::JWT_PAYLOAD => $payload,
                ]
            );
        }
    }
}
