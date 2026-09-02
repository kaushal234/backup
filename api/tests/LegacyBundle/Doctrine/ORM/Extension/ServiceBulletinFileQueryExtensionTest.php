<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Doctrine\ORM\Extension;

use App\Entity\Directory\People;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Doctrine\ORM\Extension\ServiceBulletinFileQueryExtension;
use LegacyBundle\Entity\ServiceBulletinFile;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;

class ServiceBulletinFileQueryExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testApplyFiltersLevelForNonInternalUsers(): void
    {
        $security = $this->prophesize(Security::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        // Simuler un utilisateur qui n'est pas un employé interne
        $security->getUser()->willReturn(null);

        $queryBuilder->getRootAliases()->willReturn(['o']);

        // Vérifier que le filtre sur level = 0 est ajouté
        $queryBuilder->andWhere('o.level = :level')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('level', ServiceBulletinFile::CUSTOMER_LEVEL)->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $extension = new ServiceBulletinFileQueryExtension($security->reveal());
        $extension->applyToCollection($queryBuilder->reveal(), new \ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator(), ServiceBulletinFile::class);
    }

    public function testApplyDoesNotFilterLevelForPeople(): void
    {
        $security = $this->prophesize(Security::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $user = $this->prophesize(People::class);
        $security->getUser()->willReturn($user->reveal());

        $queryBuilder->getRootAliases()->willReturn(['o']);

        // Aucune restriction de niveau pour les employés
        $queryBuilder->andWhere(Argument::containingString('level'))->shouldNotBeCalled();

        $extension = new ServiceBulletinFileQueryExtension($security->reveal());
        $extension->applyToCollection($queryBuilder->reveal(), new \ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator(), ServiceBulletinFile::class);
    }
}
