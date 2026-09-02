<?php

declare(strict_types=1);

namespace App\ION\Notifier;

use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PurchaseOrderNotifier
{
    private readonly MailerInterface $mailer;
    private readonly NormalizerInterface $normalizer;

    public function __construct(MailerInterface $mailer, NormalizerInterface $normalizer)
    {
        $this->mailer = $mailer;
        $this->normalizer = $normalizer;
    }

    public function notifyPurchaseOrderConfirmationDatesUpdate(PurchaseOrder $purchaseOrder, ?string $message = null): void
    {
        $buyer = $purchaseOrder->buyer;
        if (!$buyer || '' === $buyer->emailAddress) {
            return;
        }

        $normalizedPurchaseOrder = $this->normalizer->normalize($purchaseOrder, null, [AbstractObjectNormalizer::GROUPS => PurchaseOrder::ITEM_NORMALIZATION_GROUPS, DateTimeNormalizer::FORMAT_KEY => 'Y-m-d']);

        $email = (new TemplatedEmail())
            ->to($buyer->emailAddress)
            ->subject('purchase_order.confirmation.subject')
            ->htmlTemplate('Emails/PurchaseOrder/purchase_order_confirmation.html.twig')
            ->context([
                'purchase_order' => $normalizedPurchaseOrder,
                'purchase_order_id' => $normalizedPurchaseOrder['orderIdentifier'],
                'supplier_number' => $normalizedPurchaseOrder['buyFromSupplierCode'],
                'message' => $message,
            ]);

        $this->mailer->send($email);
    }
}
