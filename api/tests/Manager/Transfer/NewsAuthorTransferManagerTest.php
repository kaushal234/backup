<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Entity\Directory\People;
use App\Entity\News\News;
use App\Manager\Transfer\EntityTransferManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewsAuthorTransferManagerTest extends KernelTestCase
{
    private EntityManager $em;

    private EntityTransferManager $transferManager;

    private People $oldAuthor;

    private People $newAuthor;

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

        $this->oldAuthor = $this->createPeopleFixture('old-author');
        $this->newAuthor = $this->createPeopleFixture('new-author');

        $this->em->persist($this->oldAuthor);
        $this->em->persist($this->newAuthor);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $news = $this->em->getRepository(News::class)->findOneBy(['title' => 'Un tibo']);
        $this->em->remove($news);

        $oldAuthor = $this->em->getRepository(People::class)->findOneBy(['username' => 'old-author']);
        $this->em->remove($oldAuthor);

        $newAuthor = $this->em->getRepository(People::class)->findOneBy(['username' => 'new-author']);
        $this->em->remove($newAuthor);

        $this->em->flush();

        parent::tearDown();
    }

    public function testErrorTransferNewsAuthor()
    {
        $news = new News();

        $this->em->refresh($this->oldAuthor);
        $news
            ->setTitle('Un tibo')
            ->setContent('Deux tibo, trois tibo doudou')
            ->setDate((new \DateTime())->modify('-1 month'))
            ->setPeople($this->oldAuthor);

        $this->em->persist($news);
        $this->em->flush();

        $this->transferManager->transfer($this->oldAuthor, $this->newAuthor);

        $this->em->refresh($news);

        self::assertSame($this->oldAuthor, $news->getPeople());
    }

    public function testSuccessTransferNewsAuthor()
    {
        $news = new News();

        $this->em->refresh($this->oldAuthor);
        $news
            ->setTitle('Un tibo')
            ->setContent('Deux tibo, trois tibo doudou')
            ->setDate((new \DateTime())->modify('+1 month'))
            ->setPeople($this->oldAuthor);

        $this->em->persist($news);
        $this->em->flush();

        $this->transferManager->transfer($this->oldAuthor, $this->newAuthor);

        $this->em->refresh($news);

        self::assertSame($this->newAuthor, $news->getPeople());
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
