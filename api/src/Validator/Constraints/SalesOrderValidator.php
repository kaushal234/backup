<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Sales\Customer;
use App\Entity\Sales\Order;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SalesOrderValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SalesOrder || !$value instanceof Order) {
            return;
        }

        if (null === $sso = $value->getSso()) {
            return;
        }

        if (null === $value->getInforLNBusinessPartnerCode() && null !== $sso->getErp() && $sso->isErpInLN() && (null !== ($endUser = $value->getEndUser()) && !$this->isSpecificCustomer($endUser) && null !== ($buyer = $value->getBuyer()) && !$this->isSpecificCustomer($buyer))) {
            $this->context
                ->buildViolation($constraint->messageInforLnCustomer)
                ->atPath('inforLnBusinessPartnerCode')
                ->setParameter('{{ name }}', $sso->getName())
                ->addViolation()
            ;
        }

        if (null === $juridicalLocation = $value->getJuridicalLocation()) {
            return;
        }

        $ssoId = $sso->getId();
        $juridicalLocationId = $juridicalLocation->getId();

        if ($juridicalLocation === $sso->getJuridicalLocation()
            || (36 === $ssoId && 3 === $juridicalLocationId) // TLD LAC - ERP 310 - TLD America Inc., TLD Japan Co., Ltd
            || (1 === $ssoId && \in_array($juridicalLocationId, [5, 9, 11], true)) // TLD ASI - ERP 600 - TLD Asia Ltd, TLD Asia (Singapore) Pte Ltd
        ) {
            return;
        }

        $this->context->buildViolation($constraint->messageInvalidJuridicalLocation)
            ->atPath('juridicalLocation')
            ->setParameter('{{ name }}', $sso->getName())
            ->addViolation();
    }

    private function isSpecificCustomer(Customer $customer): bool
    {
        return \in_array($customer->getName(), [Customer::CUSTOMER_DEMO, Customer::CUSTOMER_PROTO, Customer::CUSTOMER_STOCK], true);
    }
}
