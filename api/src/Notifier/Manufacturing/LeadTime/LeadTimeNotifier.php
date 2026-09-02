<?php

declare(strict_types=1);

namespace App\Notifier\Manufacturing\LeadTime;

use App\Entity\Activity\Log;
use App\Entity\Manufacturing\LeadTime;
use App\Entity\Sales\SalesForecast;
use App\Repository\Common\LogRepository;
use App\Repository\Sales\SalesForecastRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LeadTimeNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly SalesForecastRepository $salesForecastRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly LogRepository $logRepository,
    ) {
    }

    public function sendUpdateEmail(LeadTime ...$leadTimes): void
    {
        $emails = [];
        foreach ($leadTimes as $leadTime) {
            $salesForecasts = $this->salesForecastRepository->findHotAndOpenByProductFamilyAndFactory($leadTime->productFamily, $leadTime->factory);
            if ([] === $salesForecasts) {
                continue;
            }

            $leadTimeLog = $this->logRepository->findResourceLastUpdate($leadTime);
            if (!$leadTimeLog instanceof Log) {
                continue;
            }

            $normalizedLeadTime = $this->normalizer->normalize($leadTime, null, ['groups' => ['lead_time', 'location_public', 'catalogue_public']]);

            /** @var SalesForecast $salesForecast */
            foreach ($salesForecasts as $salesForecast) {
                $asmEmail = $salesForecast->getAsm()->getEmail();
                $leadTimeId = $leadTime->getId();
                if (null === ($emails[$asmEmail][$leadTimeId] ?? null)) {
                    $emails[$asmEmail][$leadTimeId] = [
                        'leadTime' => $normalizedLeadTime,
                        'leadTimeChange' => $leadTimeLog->getChangeSet(),
                        'salesForecasts' => [],
                    ];
                }
                $emails[$asmEmail][$leadTimeId]['salesForecasts'][] = $salesForecast->getId();
            }
        }

        foreach ($emails as $asmEmail => $context) {
            $email = (new TemplatedEmail())
                ->to($asmEmail)
                ->subject('lead_time.subject')
                ->htmlTemplate('Emails/Manufacturing/lead_time_update.html.twig')
                ->context(['leadTimes' => $context]);
            $this->mailer->send($email);
        }
    }
}
