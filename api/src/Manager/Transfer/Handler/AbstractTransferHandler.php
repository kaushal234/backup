<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractTransferHandler implements TransferHandlerInterface
{
    protected EntityManagerInterface $em;

    protected PropertyAccessorInterface $propertyAccessor;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->em = $entityManager;

        return $this;
    }

    #[Required]
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): self
    {
        $this->propertyAccessor = $propertyAccessor;

        return $this;
    }

    protected function getQueryBuilder($source, string $className, string $propertyName): QueryBuilder
    {
        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository($className);
        $qb = $repo->createQueryBuilder('o');

        $prop = \sprintf('o.%s', $propertyName);

        $qb
            ->where($prop.' = :source')
            ->setParameters(new ArrayCollection([
                new Parameter('source', $source),
            ]));

        return $qb;
    }

    protected function update(QueryBuilder $queryBuilder, string $property, $target): void
    {
        $entities = $queryBuilder->getQuery()->getResult();
        foreach ($entities as $entity) {
            $this->em->refresh($entity);

            $this->propertyAccessor->setValue($entity, $property, $target);
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    protected function delete(QueryBuilder $queryBuilder): void
    {
        $entities = $queryBuilder->getQuery()->getResult();
        foreach ($entities as $entity) {
            $this->em->refresh($entity);
            $this->em->remove($entity);
        }

        $this->em->flush();
    }
}
