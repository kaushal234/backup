<?php

declare(strict_types=1);

namespace App\Notifier\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\ProductCertificate;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCertificateNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly LocationRepository $locationRepository,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    /**
     * @param array|ProductCertificate[] $certificates
     */
    public function sendExpiredCertificates(array $certificates): void
    {
        $recipients = [];
        // Certificate only useful to China so only WUX and SHA to notify
        foreach ($this->locationRepository->findBy(['erp' => [640, 660]]) as $factory) {
            $recipients = [...$recipients, ...$this->peopleRepository->findGroupsMembers(['ROLE_EM'], $factory)];
        }

        if ([] === $recipients) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('product_certificate.subject')
            ->htmlTemplate('Emails/Sales/Catalogue/product_certificate_expired.html.twig')
            ->context($this->buildContext($certificates));

        $this->mailer->send($email);
    }

    /**
     * @param array|ProductCertificate[] $certificates
     */
    private function buildContext(array $certificates, array $context = []): array
    {
        $productCertificatesNormalized = [];
        foreach ($certificates as $productCertificate) {
            $productCertificatesNormalized[] = $this->normalizer->normalize($productCertificate, null, [
                'groups' => [
                    'product_certificate',
                    'catalogue_public',
                    'location_public',
                    'emission_rating',
                ],
            ]);
        }

        return $context + ['productCertificates' => $productCertificatesNormalized];
    }
}
