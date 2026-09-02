<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\SpartaExtensions\Times;

use App\Entity\Directory\People;
use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionInput;
use App\ION\Serializer\IONSyncSupportTrait;
use Cake\Chronos\Chronos;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TimeKeepingInputNormalizer implements NormalizerInterface
{
    use IONSyncSupportTrait;
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    protected const ALREADY_CALLED = 'TIME_KEEPING_POST_TRANSACTION_INPUT_NORMALIZER_ALREADY_CALLED';

    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof TimeKeepingPostTransactionInput && $this->supportIONSync($context);
    }

    /** @param TimeKeepingPostTransactionInput $object */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /**
         * @var People $user
         */
        $user = $this->security->getUser();
        if ((null !== $user->getBusinessUnit()) && (null !== $user->getBusinessUnit()->getLocation())) {
            $timezone = $user->getBusinessUnit()->getLocation()->getTimeZone();
        }

        // adjust to user's timezone, if timezone is not set on its BU Location, we set it to default timezone of server
        $transactionDate = Chronos::now(new \DateTimeZone($timezone ?? 'America/New_York'));

        /*
         * if we manually provide the endDate of the transaction, it means it comes from the pio admin interface.
         * In this case, we override the date and set the time to 23:59.
         * It's a special case where the supervisor want to close all transactions of a given employee at the end of a given day.
         */
        if (null !== $object->endDate) {
            $transactionDate = Chronos::createFromFormat('Y-m-d H:i:s', $object->endDate->format('Y-m-d H:i:s'), $timezone ?? 'America/New_York');
        }

        /*
         * transactionTime key must be formatted like these examples :
         * <TransactionTime>11:55:55 +2</TransactionTime>
         * <TransactionTime>11:55:55 -4</TransactionTime>
         * +2 and -4 being the offset in hours, compared to GMT
         */
        $transactionDateOffsetInHours = (string) ($transactionDate->getOffset() / 3600);

        if (0 !== mb_strpos($transactionDateOffsetInHours, '-')) {
            $transactionTime = $transactionDate->format('H:i:s').' +'.$transactionDateOffsetInHours;
        }

        $time = $transactionTime ?? ($transactionDate->format('H:i:s').' '.($transactionDate->getOffset() / 3600));

        return [
            'Employee' => $object->employeeNumber,
            'Transaction' => $object->transactionType,
            'ProductionOrder' => $object->orderNumber,
            'Operation' => (string) $object->operationNumber,
            'Task' => $object->task,
            'Text' => \sprintf('%s. Logging info: date: %s, time: %s', $object->comment, $transactionDate->format('Y-m-d'), $time),
            'TransactionDate' => $transactionDate->format('Y-m-d'),
            'TransactionTime' => $time,
        ];
    }
}
