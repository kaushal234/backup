<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Communication\ContactCampaign;

class ContactCampaignTransferHandler extends AbstractTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(?object $source, object $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (null === $source) {
            return;
        }

        if (!$this->supports($relation->getReflectionClass()->getName(), $relation->getReflectionProperty())) {
            return;
        }

        $qb = $this->getQueryBuilder($source, $relation->getReflectionClass()->getName(), $relation->getReflectionProperty()->getName());

        $qb
            ->andWhere($qb->expr()->neq('o.status', ':closed'))
            ->setParameter('closed', ContactCampaign::CLOSED)
        ;

        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    public function getName(): string
    {
        return 'handler.contact_campaign.owner';
    }

    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return ContactCampaign::class === $className && 'owner' === $property->getName();
    }
}
