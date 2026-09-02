<?php

declare(strict_types=1);

namespace App\Controller\Sales;

use App\Entity\Sales\Customer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerStatusController
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(Customer $customer, Request $request): Customer
    {
        $content = $request->toArray();

        if (!isset($content['status'])) {
            throw new BadRequestHttpException('Status is mandatory');
        }

        $status = $content['status'];
        if (!$this->security->isGranted('FEATURE_CUSTOMER_ADMIN')
            && !$this->security->isGranted('FEATURE_CUSTOMER_STATUS')
            && !$this->security->isGranted('MOO_ECUST')
            && !(\in_array($status, [Customer::PENDING, Customer::NOT_ACTIVE], true) && $this->security->isGranted('FEATURE_CUSTOMER_EDIT'))) {
            throw new AccessDeniedException();
        }

        if (!\in_array($status, [
            Customer::PENDING,
            Customer::APPROVED,
            Customer::NOT_APPROVED,
            Customer::NOT_ACTIVE,
            Customer::PENDING_RE_APPROVAL,
        ], true)) {
            throw new BadRequestHttpException(\sprintf('Status %s is not allowed', $status));
        }

        $customer->setStatus($status);

        return $customer;
    }
}
