<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupCatalogueSyncCommand;
use App\Entity\DMS;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\ProductFamilyTag;
use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TLDGroupCatalogueSyncCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'tld:group:sync:catalogue';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWillCreateRightQueriesToWordpress()
    {
        /** @var MockObject|EntityManagerInterface $entityManagerMock */
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var MockObject|UrlGeneratorInterface $routerMock */
        $routerMock = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var MockObject|EntityRepository $productTypeRepositoryMock */
        $productTypeRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|EntityRepository $productFamilyRepositoryMock */
        $productFamilyRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManagerMock
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->withConsecutive([ProductType::class], [ProductFamily::class])
            ->willReturnOnConsecutiveCalls($productTypeRepositoryMock, $productFamilyRepositoryMock)
        ;

        /** @var MockObject|AbstractPlatform $platformMock */
        $platformMock = $this->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var MockObject|Connection $connectionMock */
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $platformMock
            ->expects(self::exactly(2))
            ->method('getTruncateTableSQL')
            ->withConsecutive(['tld_product_type'], ['tld_product']);

        $connectionMock
            ->expects(self::exactly(2))
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);

        $connectionMock
            ->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connectionMock));

        $connectionMock
            ->expects(self::exactly(2))
            ->method('executeStatement');

        $dms = (new DMS())->setLegacyId(69);
        $productTypeRepositoryMock
            ->expects(self::exactly(1))
            ->method('findBy')
            ->with(['publicForTLD' => true])
            ->willReturn([
                ($type = new ProductType())
                ->setLegacyId(1)
                ->setEnglishName('Tractor')
                ->setSpanishName('Tractoro')
                ->setFrenchName('Tracteur')
                ->setRussianName('Trakthor')
                ->setJapaneseName('Takatoukité')
                ->setChineseName('Takitaka')
                ->setPortugueseName('Tractoupelle')
                ->setGermanName('Traktor')
                ->setDms($dms),
            ]);

        $productFamily = (new ProductFamily())
            ->setLegacyId(1)
            ->setProductType($type)
            ->setEnglishDescription('Hey you !')
            ->setFrenchDescription('Coucou toi !')
            ->setSpanishDescription('Hola chico !')
            ->setRussianDescription('Vodka !')
            ->setJapaneseDescription('Bondour !')
            ->setChineseDescription('Bondour !')
            ->setPortugueseDescription('Bom dia !')
            ->setGermanDescription('Wilkommen')
            ->setName('Tractor-X')
            ->addTag((new ProductFamilyTag())->setName('Electric'))
        ;

        $productFamilyRepositoryMock
            ->expects(self::exactly(1))
            ->method('findBy')
            ->with(['publicForTLD' => true, 'hidden' => false])
            ->willReturn([$productFamily]);

        $routerMock
            ->expects(self::once())
            ->method('generate')
            ->with('dms_photo', ['id' => 69, 'd' => (new \DateTime())->format('Y-m-d')], Router::ABSOLUTE_URL)
            ->willReturn($route = 'https://www.tld-gse.com/en/private/public/dms.php&id=69&d=2020-08-11')
        ;

        $connectionMock
            ->expects(self::exactly(2))
            ->method('executeQuery')
            ->withConsecutive(
                [
                    self::callback(static fn (string $query) => 'INSERT INTO tld_product_type (id, en, fr, ru, es, pt, zh, ja, de, img_url) VALUES(:id, :en, :fr, :ru, :es, :pt, :zh, :ja, :de, :img_url)' === $query),
                    self::callback(static fn (array $parameters) => $parameters === [
                        'id' => 1,
                        'en' => 'Tractor',
                        'fr' => 'Tracteur',
                        'ru' => 'Trakthor',
                        'es' => 'Tractoro',
                        'pt' => 'Tractoupelle',
                        'zh' => 'Takitaka',
                        'ja' => 'Takatoukité',
                        'de' => 'Traktor',
                        'img_url' => $route,
                    ]),
                ],
                [
                    self::callback(static fn (string $query) => 'INSERT INTO tld_product (id, en, fr, ru, es, pt, zh, ja, de, img_url, type_id, model, drawing_url, tags) VALUES(:id, :en, :fr, :ru, :es, :pt, :zh, :ja, :de, :img_url, :type_id, :model, :drawing_url, :tags)' === $query),
                    self::callback(static fn (array $parameters) => $parameters === [
                        'id' => 1,
                        'type_id' => 1,
                        'model' => 'Tractor-X',
                        'en' => 'Hey you !',
                        'fr' => 'Coucou toi !',
                        'ru' => 'Vodka !',
                        'es' => 'Hola chico !',
                        'pt' => 'Bom dia !',
                        'zh' => 'Bondour !',
                        'ja' => 'Bondour !',
                        'de' => 'Wilkommen',
                        'img_url' => '',
                        'drawing_url' => '',
                        'tags' => 'Electric',
                    ]),
                ]
            );

        $this->application->addCommand(new TLDGroupCatalogueSyncCommand(
            $entityManagerMock,
            $connectionMock,
            $routerMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
