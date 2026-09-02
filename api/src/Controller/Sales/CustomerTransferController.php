<?php

declare(strict_types=1);

namespace App\Controller\Sales;

use App\Entity\Finance\AccountReceivable;
use App\Entity\Sales\Customer;
use App\Manager\Transfer\EntityTransferManager;
use App\Manager\Transfer\Model\TransferCustomerModel;
use App\Repository\Finance\AccountReceivableRepository;
use App\Repository\Sales\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\EquipmentRecordManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerTransferController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly EntityTransferManager $transferManager;
    private readonly EquipmentRecordManager $equipmentRecordManager;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(SerializerInterface $serializer, EntityTransferManager $transferManager, EquipmentRecordManager $equipmentRecordManager, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->transferManager = $transferManager;
        $this->equipmentRecordManager = $equipmentRecordManager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Customer $customer, Request $request)
    {
        /** @var AccountReceivableRepository $accountReceivableRepository */
        $accountReceivableRepository = $this->entityManager->getRepository(AccountReceivable::class);

        if (!empty($accountReceivableRepository->getCustomerIdentifiersForAccountReceivables($customer))) {
            throw new UnprocessableEntityHttpException('This customer is not transferable as some Account Receivables remain unpaid.');
        }

        /** @var TransferCustomerModel $transferParams */
        $transferParams = $this->serializer->deserialize($request->getContent(), TransferCustomerModel::class, JsonEncoder::FORMAT);

        $this->transferManager->transfer($customer, $transferParams->getTarget());

        $this->equipmentRecordManager->transferCustomer($customer, $transferParams->getTarget());

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->entityManager->getRepository(Customer::class);
        $customerRepository->hideCustomer($customer);
        $customerRepository->deleteCustomer($customer);

        return $customer;
    }
}
