<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Manager\Transfer\EntityTransferManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BusinessUnitRepresentativeTransferManagerTest extends KernelTestCase
{
    private EntityManager $em;

    private EntityTransferManager $transferManager;

    private People $oldRepresentative;

    private People $newRepresentative;

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

        $this->oldRepresentative = $this->createPeopleFixture('old-representative');
        $this->newRepresentative = $this->createPeopleFixture('new-representative');

        $this->em->persist($this->oldRepresentative);
        $this->em->persist($this->newRepresentative);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $businessUnit = $this->em->getRepository(BusinessUnit::class)->findOneBy(['name' => 'TLD SYL']);
        $this->em->remove($businessUnit);

        $oldRepresentative = $this->em->getRepository(People::class)->findOneBy(['username' => 'old-representative']);
        $this->em->remove($oldRepresentative);

        $newRepresentative = $this->em->getRepository(People::class)->findOneBy(['username' => 'new-representative']);
        $this->em->remove($newRepresentative);

        $this->em->flush();

        parent::tearDown();
    }

    public function testSuccessTransferBusinessUnitRepresentative()
    {
        $businessUnit = new BusinessUnit();

        /** @var Location $location */
        $location = $this->em->getRepository(Location::class)->findOneBy(['id' => 22]);
        $this->em->refresh($this->oldRepresentative);
        $businessUnit
            ->setName('TLD SYL')
            ->setLocation($location)
            ->setLegacyId(404)
            ->setRepresentative($this->oldRepresentative);

        $this->em->persist($businessUnit);
        $this->em->flush();

        $this->transferManager->transfer($this->oldRepresentative, $this->newRepresentative);

        $this->em->refresh($businessUnit);

        self::assertSame($this->newRepresentative, $businessUnit->getRepresentative());
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
