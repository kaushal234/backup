<?php

declare(strict_types=1);

namespace App\Notifier\Finance;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ManufacturingMarginNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LocationRepository $locationRepository,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function sendReport(array $manufacturingMargins, Location $factory, ?\DateTimeInterface $exportedAt = null): void
    {
        $recipients = [];
        $recipients[] = $this->peopleRepository->findGroupsMembers(['ROLE_PSM', 'ROLE_PSE', 'ROLE_PSA', 'ROLE_COO', 'ROLE_CFO', 'ROLE_FC'], $factory);

        /** @var Location $location */
        foreach ($this->locationRepository->findBy(['erp' => [900, 540, 300, 600]]) as $location) {
            $roles = 900 === $location->getErp() ? ['ROLE_CEO', 'ROLE_COO', 'ROLE_CMO'] : ['ROLE_CEO', 'ROLE_RCEO', 'ROLE_RCOO'];
            $recipients[] = $this->peopleRepository->findGroupsMembers($roles, $location);
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), array_merge(...$recipients)))
            ->subject('manufacturing_margin.subject')
            ->htmlTemplate('Emails/Finance/manufacturing_margin_upload_report.html.twig')
            ->context([
                'manufacturingMargins' => $manufacturingMargins,
                'exportedAt' => null !== $exportedAt ? $exportedAt->format('Y-m') : '',
                'factory' => $factory->getName(),
            ]);

        $this->mailer->send($email);
    }
}
