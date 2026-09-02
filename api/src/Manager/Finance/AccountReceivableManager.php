<?php

declare(strict_types=1);

namespace App\Manager\Finance;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Directory\Location;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\ExchangeRate;
use App\Factory\AccountReceivableFactory;
use App\Message\Finance\AccountReceivableDelinquent;
use App\Notifier\Finance\AccountReceivableNotifier;
use App\Repository\Finance\AccountReceivableRepository;
use App\Repository\Finance\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AccountReceivableManager
{
    private readonly AccountReceivableFactory $factory;
    private readonly ValidatorInterface $validator;
    private readonly EntityManagerInterface $entityManager;
    private readonly AccountReceivableNotifier $notifier;
    private readonly NormalizerInterface $normalizer;
    private readonly MessageBusInterface $bus;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(AccountReceivableFactory $factory, ValidatorInterface $validator, EntityManagerInterface $entityManager, AccountReceivableNotifier $notifier, NormalizerInterface $normalizer, MessageBusInterface $bus, IriConverterInterface $iriConverter)
    {
        $this->factory = $factory;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->normalizer = $normalizer;
        $this->bus = $bus;
        $this->iriConverter = $iriConverter;
    }

    public function createAccountReceivableAndSendReport(array $accountReceivablesToImport, Location $location, array $tos)
    {
        /** @var AccountReceivableRepository $repository */
        $repository = $this->entityManager->getRepository(AccountReceivable::class);
        $repository->removeForErp($location);

        /** @var ExchangeRateRepository $exchangeRateRepository */
        $exchangeRateRepository = $this->entityManager->getRepository(ExchangeRate::class);

        $totalImported = 0;

        $invalidLinesExceptions = [];
        /** @var array $data */
        foreach ($accountReceivablesToImport as $data) {
            $accountReceivableToImport = $data;

            try {
                $accountReceivable = $this->factory->createAccountReceivable($accountReceivableToImport);
            } catch (UnprocessableEntityHttpException $e) {
                // just continue, AR must not be imported
                continue;
            }

            try {
                $this->validator->validate($accountReceivable);
                $totalImported += $exchangeRateRepository->convertAmount($accountReceivable->balanceAmount, $accountReceivable->currency->getName(), ExchangeRate::TYPE_END_OF_MONTH_RATE);
            } catch (ValidationException $e) {
                $invalidLinesExceptions[] = [
                    'violations' => $e->getConstraintViolationList(),
                    'accountReceivable' => $accountReceivableToImport,
                ];
                continue;
            }

            $this->entityManager->persist($accountReceivable);
        }

        $this->entityManager->flush();

        $this->bus->dispatch(new AccountReceivableDelinquent($this->iriConverter->getIriFromResource($location)));
        $this->notifier->sendReport($tos, $invalidLinesExceptions, $totalImported);
    }
}
