<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Javelo\Event\GrantJaveloGroupAccessEvent;
use App\Javelo\EventListener\GrantJaveloGroupAccessListener;
use App\Javelo\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class GrantJaveloGroupAccessListenerTest extends TestCase
{
    /**
     * @dataProvider provideGrantAccessScenarios
     */
    public function testGrantAccessLogic(
        bool $isConcerned,
        bool $alreadyInGroup,
        bool $shouldAddAcl
    ): void {
        $group = (new Group())->setName('ACL_AUTH_JAVELO');
        $people = $this->createMock(People::class);

        $aclCollection = new ArrayCollection();
        if ($alreadyInGroup) {
            $acl = new Acl();
            $acl->setGroup($group);
            $aclCollection->add($acl);
        }

        $people->method('getAcls')->willReturn($aclCollection);

        if ($shouldAddAcl) {
            $people->expects($this->once())->method('addAcl')->with($this->isInstanceOf(Acl::class));
        } else {
            $people->expects($this->never())->method('addAcl');
        }

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('searchPeopleConcernedBySynchronization')->willReturn(
            $isConcerned ? [['id' => 42]] : []
        );

        if ($shouldAddAcl) {
            $repositoryMock = $this->createMock(EntityRepository::class);
            $repositoryMock
                ->expects($this->once())
                ->method('findOneBy')
                ->with(['name' => 'ACL_AUTH_JAVELO'])
                ->willReturn($group);

            $em = $this->createMock(EntityManagerInterface::class);
            $em
                ->expects($this->once())
                ->method('getRepository')
                ->with(Group::class)
                ->willReturn($repositoryMock);

            $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Acl::class));
        } else {
            $em = $this->createMock(EntityManagerInterface::class);
            $em->expects($this->never())->method('getRepository');
            $em->expects($this->never())->method('persist');
        }

        $listener = new GrantJaveloGroupAccessListener($em, $userRepo);
        $listener(new GrantJaveloGroupAccessEvent($people));
    }

    public static function provideGrantAccessScenarios(): array
    {
        return [
            'not concerned by sync' => [
                'isConcerned' => false,
                'alreadyInGroup' => false,
                'shouldAddAcl' => false,
            ],
            'already in group' => [
                'isConcerned' => true,
                'alreadyInGroup' => true,
                'shouldAddAcl' => false,
            ],
            'needs acl' => [
                'isConcerned' => true,
                'alreadyInGroup' => false,
                'shouldAddAcl' => true,
            ],
        ];
    }
}
