<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ReEncodeChineseCommand;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ReEncodeChineseCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    final public const COMMAND = 'api:charset:chinese';

    private PropertyAccessor $propertyAccessor;

    protected function setUp(): void
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @dataProvider provideData
     */
    public function testExecute(string $entityClass, string $attribute, string $badEncoded, string $goodEncoded)
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryProphecy = $this->prophesize(Query::class);
        $emConfigurationProphecy = $this->prophesize(Configuration::class);

        $entityObject = new $entityClass();
        $this->propertyAccessor->setValue($entityObject, $attribute, $badEncoded);
        $propertyAccessorProphecy->getValue($entityObject, $attribute)->shouldBeCalledTimes(1)->willReturn($badEncoded);
        $propertyAccessorProphecy->setValue($entityObject, $attribute, Argument::any())->shouldBeCalledTimes(1)->will(static function () use ($entityObject, $attribute, $goodEncoded) {
            $propertyAccessor = new PropertyAccessor();
            $propertyAccessor->setValue($entityObject, $attribute, $goodEncoded);
        });

        $emProphecy->getConfiguration()->shouldBeCalledTimes(1)->willReturn($emConfigurationProphecy);
        $emConfigurationProphecy->addCustomStringFunction(Argument::any(), Argument::any())->shouldBeCalledTimes(1);
        $emProphecy->getRepository($entityClass)->shouldBeCalledTimes(1)->willReturn($repositoryMock);
        $repositoryMock->expects($this->once())->method('createQueryBuilder')->with('rootAlias')->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->select('rootAlias')->shouldBeCalledTimes(1)->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->setMaxResults(Argument::any())->shouldBeCalledTimes(1)->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->orWhere(Argument::any())->shouldBeCalledTimes(3)->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->setParameter(Argument::any(), Argument::any())->shouldBeCalledTimes(3)->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->getQuery()->shouldBeCalledTimes(1)->willReturn($queryProphecy->reveal());
        $queryProphecy->getResult()->shouldBeCalledTimes(1)->willReturn([$entityObject]);
        $emProphecy->flush()->shouldBeCalled();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new ReEncodeChineseCommand($emProphecy->reveal(), $propertyAccessorProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
            'entity' => $entityClass,
            'attribute' => $attribute,
            '--limit' => 1,
            '--no-interaction' => true,
        ]);

        $this->assertSame($this->propertyAccessor->getValue($entityObject, $attribute), $goodEncoded);
    }

    public function provideData()
    {
        yield 'test valide update on activity comment' => [
            'App\Entity\Activity\Comment',
            'message',
            '¹¤×÷Áî£º604090 ¼þºÅ1047286¡ªDÓÍÆáÆÆËð',
            '工作令：604090 件号1047286—D油漆破损',
        ];
        yield 'test valide update on ncr comment' => [
            'App\Entity\Quality\NonConformity',
            'problem',
            '¹¤×÷Áî£º604090 ¼þºÅ1047286¡ªDÓÍÆáÆÆËð',
            '工作令：604090 件号1047286—D油漆破损',
        ];
    }

    /**
     * @dataProvider provideDataString
     */
    public function testReencodeChineseString(string $badEncoded, string $goodEncoded)
    {
        $method = self::getMethod('reencodeChineseString');
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $command = new ReEncodeChineseCommand($emProphecy->reveal(), $propertyAccessorProphecy->reveal());
        $this->assertSame($method->invokeArgs($command, [$badEncoded]), $goodEncoded);
    }

    public function provideDataString()
    {
        yield 'test reencode 1' => ['¹¤×÷Áî£º604090 ¼þºÅ1047286¡ªDÓÍÆáÆÆËð', '工作令：604090 件号1047286—D油漆破损'];
        yield 'test reencode 2' => ['TMX-150 Ðý×ª¾¯Ê¾µÆÄÚÎÞµÆÅÝ ±¨¸æÈË£ºÂ½À¤º£', 'TMX-150 旋转警示灯内无灯泡 报告人：陆坤海'];
        yield 'test reencode 3' => ['£¨ÖÚÁå£©´«¶¯Öá×¨ÓÃÂÝË¨£¨1068322£©Áã¼þÓëÍ¼Ö½ÒªÇó²»·û£¨ÒªÇó£ºM14¡Á1.5¡Á36mm£¬Êµ¼Ê£ºM14¡Á2¡Á40mm£©¡£', '（众铃）传动轴专用螺栓（1068322）零件与图纸要求不符（要求：M14×1.5×36mm，实际：M14×2×40mm）。'];
        yield 'test reencode 4' => ['NBL¹ö¼Ü£¨00764-02-13141£©ÍÏ¹öÍ²Ö§×ù²åÏú¿ÚÎ´Í¨', 'NBL滚架（00764-02-13141）拖滚筒支座插销口未通'];
        yield 'test reencode 5' => ['£¨¾ûÀÚ£©Ê³Æ·³µ¿Õµ÷Ö§¼Ü£¨40904RN£©²Û¸Ö¿í¶È²»ºÏ¸ñ£¨ÒªÇó£º120¡Á53¡Á5.5mm£¬Êµ¼Ê£º100¡Á50¡Á5.5mm£©£»·½¹Ü±Úºñ²»ºÏ¸ñ£¨ÒªÇó£º50¡Á50¡Á5mm£¬Êµ¼Ê£º50¡Á50¡Á2mm£©¡£', '（钧磊）食品车空调支架（40904RN）槽钢宽度不合格（要求：120×53×5.5mm，实际：100×50×5.5mm）；方管壁厚不合格（要求：50×50×5mm，实际：50×50×2mm）。'];
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('App\Command\ReEncodeChineseCommand');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
