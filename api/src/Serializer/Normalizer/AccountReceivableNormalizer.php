<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Finance\InvoiceRecord;
use App\Repository\Finance\ExchangeRateRepository;
use App\Serializer\Filter\ContextFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AccountReceivableNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ACCOUNT_RECEIVABLE_NORMALIZER_ALREADY_CALLED';
    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;
    private array $rates = [];

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AccountReceivable && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param AccountReceivable $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (!\in_array('invoice_record', $context[AbstractNormalizer::GROUPS], true) && !isset($context[ContextFilter::CONVERT_TO])) {
            return $normalizedData;
        }

        $invoiceRecord = null;
        if (\in_array('invoice_record', $context[AbstractNormalizer::GROUPS], true)) {
            $invoiceRecord = $this->entityManager->getRepository(InvoiceRecord::class)->findOneBy(['invoiceNumber' => $object->erpInvoiceNumber, 'customerErpReference' => $object->customerErpReference]);
            $normalizedData['invoiceRecord'] = $this->normalizer->normalize($invoiceRecord, null, [
                'groups' => ['invoice_record', 'customer_erp_reference', 'customer_list', 'location_public', 'country_list', 'currency'],
            ]);
        }

        if (isset($context[ContextFilter::CONVERT_TO])) {
            if ([] === $this->rates) {
                /** @var ExchangeRateRepository $exchangeRateRepository */
                $exchangeRateRepository = $this->entityManager->getRepository(ExchangeRate::class);
                $rates = [...$exchangeRateRepository->getAllCurrenciesRates(ExchangeRate::TYPE_END_OF_MONTH_RATE), ['currency' => 'EUR', 'rate' => 1]];
                $this->rates = array_column($rates, 'rate', 'currency');
            }

            /** @var Currency $currency */
            $currency = $this->iriConverter->getResourceFromIri($context[ContextFilter::CONVERT_TO]);
            $finalRate = $this->rates[$currency->getName()] ?? null;
            $objectCurrencyRate = $this->rates[$object->currency->getName()] ?? null;

            $normalizedData['balanceAmountConverted'] = $object->balanceAmountLocalCurrency / $objectCurrencyRate * $finalRate;
        }

        return $normalizedData;
    }
}
