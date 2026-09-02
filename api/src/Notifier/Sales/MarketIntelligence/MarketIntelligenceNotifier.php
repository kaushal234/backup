<?php

declare(strict_types=1);

namespace App\Notifier\Sales\MarketIntelligence;

use App\Entity\Directory\People;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\ProductType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MarketIntelligenceNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function sendCommentEmail(MarketIntelligence $marketIntelligence, People $user, string $to, string $comment): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('market_intelligence.subject')
            ->htmlTemplate('Emails/Sales/MarketIntelligence/market_intelligence_comment.html.twig')
            ->context($this->buildContext($marketIntelligence, ['comment' => $comment, 'user' => (string) $user]));

        $this->mailer->send($email);
    }

    public function sendUpdateEmail(MarketIntelligence $marketIntelligence, People $user, string $to): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('market_intelligence.subject')
            ->htmlTemplate('Emails/Sales/MarketIntelligence/market_intelligence_update.html.twig')
            ->context($this->buildContext($marketIntelligence, ['user' => (string) $user]));

        $this->mailer->send($email);
    }

    public function sendCreationEmail(MarketIntelligence $marketIntelligence, People $user, string $to): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('market_intelligence.subject')
            ->htmlTemplate('Emails/Sales/MarketIntelligence/market_intelligence_create.html.twig')
            ->context($this->buildContext($marketIntelligence, ['user' => (string) $user]));

        $this->mailer->send($email);
    }

    private function buildContext(MarketIntelligence $marketIntelligence, array $context = []): array
    {
        $productTypes = $marketIntelligence->getProductTypes();
        $customers = $marketIntelligence->getCustomers();
        $competitors = $marketIntelligence->getCompetitors();

        $components = [];
        if (!$productTypes->isEmpty()) {
            /** @var ProductType $productType */
            $productType = $productTypes->first();
            if (1 === $productTypes->count()) {
                $components[] = $productType->getEnglishName();
            } else {
                $components[] = \sprintf('%s (+%d more product type(s))', $productType->getEnglishName(), $productTypes->count() - 1);
            }
        }

        if (!$customers->isEmpty()) {
            /** @var Customer $customer */
            $customer = $marketIntelligence->getCustomers()->first();
            if (1 === $customers->count()) {
                $components[] = $customer->getName();
            } else {
                $components[] = \sprintf('%s (+%d more customer(s))', $customer->getName(), $customers->count() - 1);
            }
        }

        if (!$competitors->isEmpty()) {
            /** @var Competitor $competitor */
            $competitor = $marketIntelligence->getCompetitors()->first();
            if (1 === $competitors->count()) {
                $components[] = $competitor->getName();
            } else {
                $components[] = \sprintf('%s (+%d more competitor(s))', $competitor->getName(), $competitors->count() - 1);
            }
        }

        return $context + [
            'id' => (string) $marketIntelligence->getId(),
            'type' => $marketIntelligence->getType()->name,
            'short_description' => $marketIntelligence->getShortDescription(),
            'components' => implode(' - ', $components),
            'marketIntelligence' => $this->normalizer->normalize($marketIntelligence, null, [
                'groups' => [
                    'market_intelligence',
                    'people_public',
                    'customer_list',
                    'competitor_list',
                    'catalogue_type_list',
                ],
            ]),
        ];
    }
}
