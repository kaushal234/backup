<?php

declare(strict_types=1);

namespace App\Repository\Module;

use App\Entity\Module\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    /**
     * @return Module|null
     */
    public function findByName(string $name): ?object
    {
        return $this->findOneBy(['name' => mb_strtoupper($name)]);
    }

    public function convertType(Module $module, string $targetType): void
    {
        $query = <<<SQL
                UPDATE modules
                SET
                    discr = '$targetType',
                    sso = false,
                    mfa_user = false,
                    mfa_admin = false
                WHERE id = {$module->getId()}
            SQL;
        $this->getEntityManager()->getConnection()->executeQuery($query);
    }
}
