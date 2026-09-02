<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\Currency;
use App\Entity\Finance\TransactionType;
use App\Entity\Finance\TransactionTypeReference;
use App\Entity\Sales\CustomerErpReference;
use App\Entity\Sales\CustomerType;
use App\Manager\EntityDictionaryManager;
use App\Repository\Sales\OrderRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AccountReceivableFactory
{
    private readonly EntityDictionaryManager $dictionary;
    private readonly OrderRepository $orderRepository;

    public function __construct(EntityDictionaryManager $dictionary, OrderRepository $orderRepository)
    {
        $this->dictionary = $dictionary;
        $this->orderRepository = $orderRepository;
    }

    public function createAccountReceivable(array $accountReceivableArray): AccountReceivable
    {
        $locationDictionary = $this->dictionary->getIndexedTable(Location::class, ['erp']);
        $countryDictionary = $this->dictionary->getIndexedTable(Country::class, ['isoCode2']);
        $currencyDictionary = $this->dictionary->getIndexedTable(Currency::class, ['name']);
        $transactionTypeReferenceDictionary = $this->dictionary->getIndexedTable(TransactionTypeReference::class, ['erpType', 'location']);
        $customerErpReferenceDictionary = $this->dictionary->getIndexedTable(CustomerErpReference::class, ['sso', 'customerNumber']);

        if ('RMB' === $accountReceivableArray['currency']) {
            $accountReceivableArray['currency'] = 'CNY';
        }

        $country = isset($accountReceivableArray['country']) ? ($countryDictionary[$accountReceivableArray['country']] ?? null) : null;
        $location = isset($accountReceivableArray['erp']) ? ($locationDictionary[$accountReceivableArray['erp']] ?? null) : null;
        $currency = isset($accountReceivableArray['currency']) ? ($currencyDictionary[$accountReceivableArray['currency']] ?? null) : null;
        $transactionTypeReference = $transactionTypeReferenceDictionary[\sprintf('%s%s', $accountReceivableArray['transactionType'], $location->getName())] ?? null;
        /** @var CustomerErpReference|null $customerErpReference */
        $customerErpReference = $customerErpReferenceDictionary[\sprintf('%s%s', $location->getName(), $accountReceivableArray['pcust'])] ?? null;

        if ((320 === (int) $accountReceivableArray['erp'] && null !== $customerErpReference && 0 === $customerErpReference->getCustomer()->getCustomerTypes()->filter(static fn (CustomerType $customerType) => CustomerType::MILITARY_TYPE_NAME === $customerType->getName())->count())
            || (\in_array((int) $accountReceivableArray['erp'], [300, 310], true) && null !== $customerErpReference && $customerErpReference->getCustomer()->getCustomerTypes()->filter(static fn (CustomerType $customerType) => CustomerType::MILITARY_TYPE_NAME === $customerType->getName())->count() > 0)
        ) {
            throw new UnprocessableEntityHttpException();
        }

        $order = null;
        if (null !== $transactionTypeReference && TransactionType::UNITS === $transactionTypeReference->transactionType->name && null !== $accountReceivableArray['salesOrderNumber']) {
            try {
                $order = $this->orderRepository->findOrderForAccountReceivable($location, $accountReceivableArray);
            } catch (NonUniqueResultException $e) {
                // do nothing, order is null
            }
        }

        $accountReceivable = new AccountReceivable();
        foreach (['country' => $country, 'currency' => $currency, 'customerErpReference' => $customerErpReference, 'transactionTypeReference' => $transactionTypeReference, 'order' => $order] as $key => $value) {
            if (null === $value) {
                continue;
            }

            $accountReceivable->{$key} = $value;
        }

        $accountReceivable->erpInvoiceNumber = $accountReceivableArray['erpInvoiceNumber'];
        $accountReceivable->balanceAmount = $accountReceivableArray['balanceAmount'];
        $accountReceivable->balanceAmountLocalCurrency = $accountReceivableArray['balanceAmountLocalCurrency'] ?? null;
        $accountReceivable->originalAmount = $accountReceivableArray['originalAmount'];
        $accountReceivable->originalAmountLocalCurrency = $accountReceivableArray['originalAmountLocalCurrency'];
        $accountReceivable->creditAnalyst = $accountReceivableArray['creditAnalyst'] ?? null;
        $accountReceivable->dueDate = isset($accountReceivableArray['dueDate']) ? new \DateTime($accountReceivableArray['dueDate']) : null;
        $accountReceivable->financeReferenceA = $accountReceivableArray['financeReferenceA'] ?? null;
        $accountReceivable->financeReferenceB = $accountReceivableArray['financeReferenceB'] ?? null;
        $accountReceivable->salesReferenceA = $accountReceivableArray['salesReferenceA'] ?? null;
        $accountReceivable->salesReferenceB = $accountReceivableArray['salesReferenceB'] ?? null;
        $accountReceivable->salesOrderDate = isset($accountReceivableArray['salesOrderDate']) ? new \DateTime($accountReceivableArray['salesOrderDate']) : null;
        $accountReceivable->purchaseOrderNumber = $accountReceivableArray['purchaseOrderNumber'] ?? null;
        $accountReceivable->salesOrderNumber = $accountReceivableArray['salesOrderNumber'] ?? null;
        $accountReceivable->invoiceDate = new \DateTime($accountReceivableArray['invoiceDate']);

        return $accountReceivable;
    }
}
