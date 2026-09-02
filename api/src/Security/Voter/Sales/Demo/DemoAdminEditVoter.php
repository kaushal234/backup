<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DemoAdminEditVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[EntityManagerInterface::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'DEMO_ADMIN_EDIT_VOTER' === $attribute && $subject instanceof Demo;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if (null === $ast = $subject->getAst()) {
            return false;
        }

        $uow = $this->serviceLocator->get(EntityManagerInterface::class)->getUnitOfWork();
        $uow->computeChangeSets();

        $changeset = $uow->getEntityChangeSet($subject);

        if (isset($changeset['ast'])) {
            $ast = $changeset['ast'][0];
        }

        /** @var People|null $supervisor */
        $supervisor = $ast->getSupervisor();

        return (null !== $supervisor && $user->getId() === $supervisor->getId()) || $this->getSecurity()->isGranted('FEATURE_DEMO_ADMIN');
    }
}
