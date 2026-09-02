<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Manager\Transfer\EntityTransferManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocationRepresentativeTransferManagerTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private EntityTransferManager $transferManager;

    private People $oldRepresentative;

    private People $newRepresentative;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
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
        $oldRepresentative = $this->em->getRepository(People::class)->findOneBy(['username' => 'old-representative']);
        $this->em->remove($oldRepresentative);

        $newRepresentative = $this->em->getRepository(People::class)->findOneBy(['username' => 'new-representative']);
        $this->em->remove($newRepresentative);

        $division = $this->em->getRepository(Location::class)->findOneBy(['name' => 'TLD AUS']);
        $this->em->remove($division);

        $this->em->flush();

        parent::tearDown();
    }

    public function testSuccessTransferLocationRepresentative()
    {
        $location = new Location();

        $this->em->refresh($this->oldRepresentative);
        $location
            ->setName('TLD AUS')
            ->setCompany('TLD Australia')
            ->setRepresentative($this->oldRepresentative);

        $this->em->persist($location);
        $this->em->flush();

        $this->transferManager->transfer($this->oldRepresentative, $this->newRepresentative);

        $this->em->refresh($location);

        self::assertSame($this->newRepresentative, $location->getRepresentative());
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
