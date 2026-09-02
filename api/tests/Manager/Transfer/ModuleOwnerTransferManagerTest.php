<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Manager\Transfer\EntityTransferManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ModuleOwnerTransferManagerTest extends KernelTestCase
{
    private EntityManager $em;

    private EntityTransferManager $transferManager;

    private People $oldOwner;

    private People $newOwner;

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

        $this->oldOwner = $this->createPeopleFixture('old-owner');
        $this->newOwner = $this->createPeopleFixture('new-owner');

        $this->em->persist($this->oldOwner);
        $this->em->persist($this->newOwner);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $division = $this->em->getRepository(Module::class)->findOneBy(['name' => 'MST']);
        $this->em->remove($division);

        $oldOwner = $this->em->getRepository(People::class)->findOneBy(['username' => 'old-owner']);
        $this->em->remove($oldOwner);

        $newOwner = $this->em->getRepository(People::class)->findOneBy(['username' => 'new-owner']);
        $this->em->remove($newOwner);

        $this->em->flush();

        parent::tearDown();
    }

    public function testSuccessTransferModuleOperationalOwner()
    {
        $module = new Module();

        $this->em->refresh($this->oldOwner);
        $module
            ->setName('MST')
            ->setFullDescription('Master Scheduled Tasks')
            ->setShortDescription('Protect yourself')
            ->setOperationalOwner($this->oldOwner);

        $this->em->persist($module);
        $this->em->flush();

        $this->transferManager->transfer($this->oldOwner, $this->newOwner);

        $this->em->refresh($module);

        self::assertSame($this->newOwner, $module->getOperationalOwner());
    }

    public function testSuccessTransferModuleMisOwner()
    {
        $module = new Module();

        $this->em->refresh($this->oldOwner);
        $module
            ->setName('MST')
            ->setFullDescription('Master Scheduled Tasks')
            ->setShortDescription('Protect yourself')
            ->setMisOwner($this->oldOwner);

        $this->em->persist($module);
        $this->em->flush();

        $this->transferManager->transfer($this->oldOwner, $this->newOwner);

        $this->em->refresh($module);

        self::assertSame($this->newOwner, $module->getMisOwner());
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
