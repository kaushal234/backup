<?php

declare(strict_types=1);

namespace App\Filter\ExtranetUser;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\TypeInfo\TypeIdentifier;

readonly class ExtranetUserRelatedToCustomerFilter implements FilterInterface
{
    public const FILTER_CUSTOMER_PROPERTY = 'relatedToCustomer';

    public function __construct(
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (ExtranetUser::class !== $resourceClass) {
            throw new \LogicException('This filter is restricted to the ExtranetUser resource.');
        }

        if (!isset($context['request']) || !($request = $context['request']) instanceof Request) {
            return;
        }

        if (!$request->query->has(static::FILTER_CUSTOMER_PROPERTY)) {
            return;
        }

        try {
            $customer = $this->iriConverter->getResourceFromIri($request->query->get(static::FILTER_CUSTOMER_PROPERTY));
        } catch (ItemNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $expr = $queryBuilder->expr();

        $queryBuilder
            ->join(\sprintf('%s.extranetUserProfile', $rootAlias), 'ep')
            ->andWhere($expr->eq('ep.archived', 'false'))
        ;

        $subQb = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subAlias = 'acl_sub';
        $subExpr = $subQb->expr();

        $subQb
            ->select("IDENTITY($subAlias.extranetUser)")
            ->from('App\Entity\Sales\ExtranetUserAcl', $subAlias)
            ->join("$subAlias.extranetUserGroup", 'group_sub')
            ->join("$subAlias.crt", 'crt_sub')
            ->where($subExpr->in('group_sub.name', ':allowedRoles'))
            ->andWhere($subExpr->eq(':customerId', 'crt_sub.customer'))
            ->andWhere('crt_sub.deletedAt IS NULL')
        ;

        $queryBuilder->andWhere(
            $expr->in("$rootAlias.id", $subQb->getDQL())
        );

        $queryBuilder
            ->setParameter('allowedRoles', ['role_ST', 'fl_NOT_TOC'])
            ->setParameter('customerId', $customer->getId())
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (ExtranetUser::class !== $resourceClass) {
            return [];
        }

        return [
            'relatedToCustomer' => [
                'property' => 'relatedToCustomer',
                'type' => TypeIdentifier::STRING->value,
                'required' => false,
                'swagger' => [
                    'description' => 'Returns all ExtranetUsers linked a customer by CRT',
                    'name' => 'relatedToCustomer',
                    'type' => 'string',
                ],
            ],
        ];
    }
}
