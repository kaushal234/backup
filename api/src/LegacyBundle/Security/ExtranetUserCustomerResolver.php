<?php

declare(strict_types=1);

namespace LegacyBundle\Security;

use App\Entity\Sales\ExtranetUser;
use Doctrine\DBAL\Connection;

/**
 * Resolves the legacy customer ids an extranet user is allowed to access, through their ACLs
 * (extranet_users_roles -> customers_crt -> customer). These mapping tables are not mapped as
 * entities in the legacy EM, so we rely on raw DBAL.
 */
final readonly class ExtranetUserCustomerResolver
{
    public function __construct(
        private Connection $legacyConnection,
    ) {
    }

    /**
     * @return list<int>
     */
    public function getAccessibleCustomerLegacyIds(ExtranetUser $user): array
    {
        $customerLegacyIds = $this->legacyConnection->fetchFirstColumn(
            'SELECT DISTINCT cc.customer_id
             FROM extranet_users_roles eur
             JOIN customers_crt cc ON cc.id = eur.crt_id
             WHERE eur.parent_id = :legacyUserId',
            ['legacyUserId' => $user->getLegacyId()],
        );

        return array_values(array_map(intval(...), array_filter($customerLegacyIds)));
    }
}
