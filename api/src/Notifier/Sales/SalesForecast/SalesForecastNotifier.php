<?php

declare(strict_types=1);

namespace App\Notifier\Sales\SalesForecast;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\SalesForecast;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesForecastNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly Security $security,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function sendEmail(SalesForecast $salesForecast, string $subject, string $template, array $changeset = []): void
    {
        /** @var User $user */
        $user = $this->security->getUser() ?? $salesForecast->getAsm();

        $context = [];
        if (\in_array($salesForecast->getStatus(), SalesForecast::CLOSED_STATUSES, true)) {
            $context = $this->getClosureContext($salesForecast);
        }

        $context['changeSet'] = $changeset;
        $email = (new TemplatedEmail())
            ->from($user->getEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findRecipients($salesForecast, $subject)))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findCcs($salesForecast, $subject)))
            ->subject($subject)
            ->htmlTemplate(\sprintf('Emails\Sales\SalesForecast\%s', $template))
            ->context($this->buildContext($salesForecast, $user, $context));

        $this->mailer->send($email);
    }

    public function sendMasterEdition(SalesForecast $salesForecast, array $changeset)
    {
        /** @var User $user */
        $user = $this->security->getUser() ?? $salesForecast->getAsm();

        $email = (new TemplatedEmail())
            ->from($user->getEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findRecipients($salesForecast)))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findCcs($salesForecast)))
            ->subject('sfr.subject.master_edition')
            ->htmlTemplate('Emails\Sales\SalesForecast\master_sales_forecast_edition.html.twig')
            ->context($this->buildContext($salesForecast, $user, array_merge(['changeSet' => $changeset], $this->getMasterContext($salesForecast))));

        $this->mailer->send($email);
    }

    public function sendDelinquent(array $salesForecasts, People $asm, Location $sso)
    {
        $email = (new TemplatedEmail())
            ->to($asm->getEmail())
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findCcsDelinquent($sso, $asm)))
            ->subject('sfr.subject.delinquent')
            ->htmlTemplate('Emails\Sales\SalesForecast\sales_forecast_delinquent.html.twig')
            ->context(['asm' => (string) $asm, 'salesForecasts' => $this->normalizer->normalize($salesForecasts, null, [
                'groups' => [
                    'sales_forecast_detail', 'expose_legacy', 'location_public', 'product_list', 'people_public', 'customer_public', 'customer:status', 'customer:watch', 'iata_code', 'emission_rating', 'file', 'sales_forecast:notification',
                ],
            ]),
            ]);

        $this->mailer->send($email);
    }

    private function getMasterContext(SalesForecast $salesForecast): array
    {
        if (null === $masterSalesForecast = $salesForecast->getMasterSalesForecast()) {
            return [];
        }

        return [
            'linked_sales_forecasts_normalized' => $this->normalizer->normalize(
                array_filter($masterSalesForecast->getSalesForecasts()->toArray(), static fn (SalesForecast $sfr) => \in_array($sfr->getStatus(), SalesForecast::OPEN_STATUSES, true)),
                null,
                ['groups' => ['sales_forecast_detail', 'expose_legacy', 'location_public', 'product_list', 'people_public', 'customer_public', 'customer:status', 'iata_code', 'emission_rating']]
            ),
        ];
    }

    private function getClosureContext(SalesForecast $salesForecast): array
    {
        $forecastClosures = [];
        $competitorPricings = [];
        foreach ($salesForecast->getForecastClosures() as $forecastClosure) {
            $normalized = $this->normalizer->normalize($forecastClosure, null, [
                'groups' => ['forecast_closure_detail', 'expose_legacy', 'people_public', 'file', 'competitor_public', 'sales_forecast_public', 'location_public', 'customer_public', 'customer:status', 'catalogue_public', 'country'],
            ]);
            $normalized['translatedReason'] = $this->translator->trans('fcr.fields.reasons.'.($normalized['reason'] ?? ''), [], 'emails');
            if (null === $normalized['competitor']) {
                $sso = $salesForecast->getSso();
                if (\in_array($forecastClosure->getStatus(), [ForecastClosure::PARTIAL_ORDERED, SalesForecast::ORDERED], true)) {
                    $normalized['competitor'] = ['name' => null !== $sso->getNetwork() ? $sso->getNetwork()->getName() : 'ALVEST'];
                } else {
                    $normalized['competitor'] = ['name' => 'Unknown'];
                }
            }
            $forecastClosures[] = $normalized;

            foreach ($forecastClosure->getCompetitorPricings() as $competitorPricing) {
                $competitorPricings[] = $this->normalizer->normalize($competitorPricing, null, [
                    'groups' => ['competitor_pricing_detail', 'competitor_pricing', 'expose_legacy', 'people_public', 'file', 'competitor_public', 'location_public', 'customer_public', 'customer:status', 'catalogue_public', 'sales_forecast_public'],
                ]);
            }
        }

        return [
            'sales_forecast_closures_normalized' => $forecastClosures,
            'competitor_pricings_normalized' => $competitorPricings,
        ];
    }

    private function buildContext(SalesForecast $salesForecast, User $user, $context = []): array
    {
        $successPercentage = $salesForecast->getCustomerSuccessPercentage() * $salesForecast->getSuccessPercentage() / 100;

        if (isset($context['changeSet'])) {
            $changeSet = $context['changeSet'];
            foreach ($changeSet as $key => $value) {
                $translationPath = 'sfr.change_set.'.$key;
                if ($translationPath !== $newKey = $this->translator->trans($translationPath, [], 'emails')) {
                    $changeSet[$newKey] = $value;
                    unset($changeSet[$key]);
                }
            }
            $context['changeSet'] = $changeSet;
        }

        return $context + [
            'user' => (string) $user,
            'sales_forecast_normalized' => $this->normalizer->normalize($salesForecast, null, [
                'groups' => [
                    'sales_forecast_detail', 'expose_legacy', 'location_public', 'product_list', 'people_public', 'customer_public', 'customer:status', 'customer:watch', 'iata_code', 'emission_rating', 'file', 'sales_forecast:notification',
                ],
            ]),
            'restricted' => $salesForecast->isNotificationRestricted(),
            'buyer_short' => null !== $salesForecast->getBuyer() ? mb_substr($salesForecast->getBuyer()->getName(), 0, 15) : '',
            'model' => null !== $salesForecast->getProduct() ? $salesForecast->getProduct()->getName() : '',
            'quantity' => (string) $salesForecast->getQuantity(),
            'month_year' => $salesForecast->getEstimatedSaleDate()->format('m-Y'),
            'success_percentage' => (string) round($successPercentage),
            'id' => (string) $salesForecast->getId(),
            'status' => $salesForecast->getStatus(),
            'asm' => (string) $salesForecast->getAsm(),
        ];
    }
}
