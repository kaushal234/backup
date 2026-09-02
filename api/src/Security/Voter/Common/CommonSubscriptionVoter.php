<?php

declare(strict_types=1);

namespace App\Security\Voter\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Legal\Contract;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Task\Task;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CommonSubscriptionVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[IriConverterInterface::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['SUBSCRIPTION_DELETE_VOTER', 'SUBSCRIPTION_CREATE_VOTER'], true)
            && $subject instanceof Subscription
            && (
                0 === mb_strpos($subject->getResource(), '/quality/supplier_corrective_action_requests')
                || 0 === mb_strpos($subject->getResource(), '/mis/trouble_tickets')
                || 0 === mb_strpos($subject->getResource(), '/tasks')
                || 0 === mb_strpos($subject->getResource(), '/part_number_tasks')
                || 0 === mb_strpos($subject->getResource(), '/contracts')
                || 0 === mb_strpos($subject->getResource(), '/service/technician_on_calls')
                || (bool) preg_match('/purchasing\/(ncr_|wc_|)vendor_warranty_claims/', $subject->getResource())
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $subscriber = $subject->getUser();

        if (!$subscriber instanceof People) {
            return false;
        }

        try {
            $item = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($subject->getResource());
        } catch (\Exception $exception) {
            return false;
        }

        if (!$item instanceof SupplierCorrectiveActionRequest
            && !$item instanceof VendorWarrantyClaim
            && !$item instanceof TroubleTicket
            && !$item instanceof Task
            && !$item instanceof Contract
            && !$item instanceof TechnicianOnCall
        ) {
            return false;
        }

        $user = $token->getUser();

        return $user instanceof People;
    }
}
