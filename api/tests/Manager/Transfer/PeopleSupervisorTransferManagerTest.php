<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Entity\Directory\People;
use App\Manager\Transfer\EntityTransferManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PeopleSupervisorTransferManagerTest extends KernelTestCase
{
    private EntityManager $em;

    private EntityTransferManager $transferManager;

    private People $oldSupervisor;

    private People $newSupervisor;

    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel();

        /** @var EntityManager $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->em = $em;
        /** @var EntityTransferManager $transferManager */
        $transferManager = static::getContainer()->get('app.transfer.manager.generic');
        $this->transferManager = $transferManager;

        $this->oldSupervisor = $this->createPeopleFixture('old-supervisor');
        $this->newSupervisor = $this->createPeopleFixture('new-supervisor');

        $this->em->persist($this->oldSupervisor);
        $this->em->persist($this->newSupervisor);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $teamMember = $this->em->getRepository(People::class)->findOneBy(['username' => 'team-member']);
        $this->em->remove($teamMember);

        $oldSupervisor = $this->em->getRepository(People::class)->findOneBy(['username' => 'old-supervisor']);
        $this->em->remove($oldSupervisor);

        $newSupervisor = $this->em->getRepository(People::class)->findOneBy(['username' => 'new-supervisor']);
        $this->em->remove($newSupervisor);

        $this->em->flush();

        parent::tearDown();
    }

    public function testSuccessTransferPeopleSupervisor()
    {
        $teamMember = $this->createPeopleFixture('team-member');

        $this->em->refresh($this->oldSupervisor);
        $teamMember->setSupervisor($this->oldSupervisor);

        $this->em->persist($teamMember);
        $this->em->flush();

        $this->transferManager->transfer($this->oldSupervisor, $this->newSupervisor);

        $this->em->refresh($teamMember);

        self::assertSame($this->newSupervisor, $teamMember->getSupervisor());
    }

    public function testErrorTransferHiddenPeopleSupervisor()
    {
        $teamMember = $this->createPeopleFixture('team-member');

        $this->em->refresh($this->oldSupervisor);
        $teamMember->setHidden(true);
        $teamMember->setSupervisor($this->oldSupervisor);

        $this->em->persist($teamMember);
        $this->em->flush();

        $this->transferManager->transfer($this->oldSupervisor, $this->newSupervisor);

        $this->em->refresh($teamMember);

        self::assertSame($this->oldSupervisor, $teamMember->getSupervisor());
    }

    public function testErrorTransferDisabledPeopleSupervisor()
    {
        $teamMember = $this->createPeopleFixture('team-member');

        $this->em->refresh($this->oldSupervisor);
        $teamMember->setDisabled(true);
        $teamMember->setSupervisor($this->oldSupervisor);

        $this->em->persist($teamMember);
        $this->em->flush();

        $this->transferManager->transfer($this->oldSupervisor, $this->newSupervisor);

        $this->em->refresh($teamMember);

        self::assertSame($this->oldSupervisor, $teamMember->getSupervisor());
    }

    private function createPeopleFixture(string $salt): People
    {
        $fixture = new People();

        $fixture
            ->setEmail(\sprintf('%s@tld-gse.com', $salt))
            ->setUsername($salt)
            ->setFirstname('Patrick')
            ->setLastname('Juvet')
            ->setEncodedPassword(md5($salt))
            ->setSalt($salt)
            ->setHidden(false)
            ->setDisabled(false);

        return $fixture;
    }
}
