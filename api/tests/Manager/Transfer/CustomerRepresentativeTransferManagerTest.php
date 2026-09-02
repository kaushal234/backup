<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\SubDivision;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MainSalesRepresentative;
use App\Manager\Transfer\EntityTransferManager;
use Doctrine\ORM\EntityManager;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomerRepresentativeTransferManagerTest extends KernelTestCase
{
    private EntityManager $em;
    private EntityTransferManager $customerTransferManager;
    private EntityTransferManager $transferManager;
    private People $oldRepresentative;
    private People $newRepresentative;
    private SubDivision $subDivision;

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

        /** @var EntityTransferManager $customerTransferManager */
        $customerTransferManager = static::getContainer()->get('app.transfer.manager.customer_representative');
        $this->customerTransferManager = $customerTransferManager;

        $this->oldRepresentative = $this->createPeopleFixture('old-representative');
        $this->em->persist($this->oldRepresentative);
        $this->newRepresentative = $this->createPeopleFixture('new-representative');
        $this->em->persist($this->newRepresentative);
        $division = new Division();
        $division->name = 'division_name';
        $this->subDivision = new SubDivision();
        $this->subDivision->name = 'subdivision_name';
        $this->subDivision->division = $division;
        $this->em->persist($division);
        $this->em->persist($this->subDivision);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        // remove softdeleteable extension to be able to really remove rows from database
        $doctrineEventManager = $this->em->getEventManager();

        /** @var array $eventManagerListeners */
        $eventManagerListeners = $doctrineEventManager->getAllListeners();
        foreach ($eventManagerListeners as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof SoftDeleteableListener) {
                    $this->em->getEventManager()->removeEventListener($eventName, $listener);
                }
            }
        }

        $oldAuthor = $this->em->getRepository(People::class)->findOneBy(['username' => 'old-representative']);
        $this->em->remove($oldAuthor);
        $newAuthor = $this->em->getRepository(People::class)->findOneBy(['username' => 'new-representative']);
        $this->em->remove($newAuthor);
        $customer = $this->em->getRepository(Customer::class)->findOneBy(['name' => 'huit name']);
        $this->em->remove($customer);
        $division = $this->em->getRepository(Division::class)->findOneBy(['name' => 'division_name']);
        $this->em->remove($division);
        $subDivision = $this->em->getRepository(SubDivision::class)->findOneBy(['name' => 'subdivision_name']);
        $this->em->remove($subDivision);
        $this->em->flush();

        parent::tearDown();
    }

    public function testWithBadCustomerTransferHandler()
    {
        $customer = new Customer();

        $mainSalesRepresentative = new MainSalesRepresentative();
        $mainSalesRepresentative->asm = $this->oldRepresentative;
        $mainSalesRepresentative->subDivision = $this->subDivision;
        $this->em->persist($mainSalesRepresentative);

        $this->em->refresh($this->oldRepresentative);
        $customer
            ->setName('huit name')
            ->setMainSalesRepresentative($mainSalesRepresentative);

        $this->em->persist($customer);
        $this->em->flush();

        $this->transferManager->transfer($this->oldRepresentative, $this->newRepresentative);

        $this->em->refresh($customer);

        self::assertSame($this->oldRepresentative, $customer->getMainSalesRepresentative()->asm, 'The generic transfer manager should not handle this transfer');
    }

    public function testWithGoodCustomerTransferHandler()
    {
        $customer = new Customer();

        $mainSalesRepresentative = new MainSalesRepresentative();
        $mainSalesRepresentative->asm = $this->oldRepresentative;
        $mainSalesRepresentative->subDivision = $this->subDivision;
        $this->em->persist($mainSalesRepresentative);

        $this->em->refresh($this->oldRepresentative);
        $customer
            ->setName('huit name')
            ->setMainSalesRepresentative($mainSalesRepresentative);

        $this->em->persist($customer);
        $this->em->flush();

        $this->customerTransferManager->transfer($this->oldRepresentative, $this->newRepresentative);

        $this->em->refresh($customer);

        self::assertSame($this->newRepresentative, $customer->getMainSalesRepresentative()->asm, 'The custom transfer manager should handle this transfer');
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
