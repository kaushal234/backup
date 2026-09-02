<?php

declare(strict_types=1);

namespace App\Notifier\Finance;

use App\Repository\Finance\ExchangeRateRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Validator\ConstraintViolation;

class AccountReceivableNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ExchangeRateRepository $exchangeRateRepository,
    ) {
    }

    public function sendReport(array $tos, array $exceptions, float $totalImported): void
    {
        if ([] === $tos) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...$tos)
            ->subject('account_receivable.subject')
            ->htmlTemplate('Emails/Finance/AccountReceivable/account_receivable_import.html.twig')
            ->context($this->buildContext($exceptions, ['imported' => $totalImported / 1000]));

        $this->mailer->send($email);
    }

    private function buildContext(array $exceptions, $context = []): array
    {
        $totalNotImported = 0;
        $exceptionCount = 0;
        $i = 0;
        $truncatedErrors = false;
        foreach ($exceptions as $exception) {
            $errorMessage = '';
            /** @var ConstraintViolation $violation */
            foreach ($exception['violations'] as $violation) {
                $errorMessage = \sprintf("%s \n%s : %s", $errorMessage, $violation->getPropertyPath(), $violation->getMessage());
            }

            $accountReceivable = $exception['accountReceivable'];

            try {
                $totalNotImported += $this->exchangeRateRepository->convertAmount($accountReceivable['balanceAmount'], 'EUR');
            } catch (\InvalidArgumentException $e) {
                ++$exceptionCount;
            }

            ++$i;
            if ($i >= 100) {
                $truncatedErrors = true;
                continue;
            }

            $context['errors'][] = [
                'erp' => $accountReceivable['erp'],
                'pcust' => $accountReceivable['pcust'],
                'customer' => $accountReceivable['customerName'],
                'transactionType' => $accountReceivable['transactionType'],
                'invoiceNumber' => $accountReceivable['erpInvoiceNumber'],
                'balanceAmount' => $accountReceivable['balanceAmount'],
                'currency' => $accountReceivable['currency'],
                'errors' => $errorMessage,
            ];
        }

        return $context + ['totalNotImported' => $totalNotImported / 1000, 'exceptionCount' => $exceptionCount, 'truncatedErrors' => $truncatedErrors, 'errorLines' => \count($exceptions)];
    }
}
